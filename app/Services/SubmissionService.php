<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\Form;
use App\Models\AffiliateReward;
use App\Models\PricingTier;
use App\Models\Upsell;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubmissionService
{
    public function __construct(
        protected XenditService $xenditService
    ) {
    }

    public function submitForm(array $data, $user = null): Submission
    {
        return DB::transaction(function () use ($data, $user) {
            // Get the form
            $form = Form::with(['sections.fields', 'pricingTiers', 'upsells'])
                ->where('slug', $data['form_slug'])
                ->where('is_active', true)
                ->firstOrFail();

            // Handle file uploads
            $formData = $this->handleFileUploads($form, $data['data']);

            // Validate form fields
            $this->validateFormData($form, $formData);

            // For authenticated submissions, use user's email and info
            if ($user) {
                // Check if this user has already submitted this form
                $existingSubmission = Submission::where('form_id', $form->id)
                    ->where('email', $user->email)
                    ->first();

                if ($existingSubmission) {
                    throw ValidationException::withMessages([
                        'email' => ['Anda sudah pernah mengisi form ini sebelumnya.'],
                    ]);
                }

                // Use user's info
                $contactInfo = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $formData['phone'] ?? $formData['whatsapp'] ?? null,
                ];
            } else {
                // For public submissions (if any remain), extract from form data
                $contactInfo = $this->extractContactInfo($formData);

                // Check if this email has already submitted this form
                if (!empty($contactInfo['email'])) {
                    $existingSubmission = Submission::where('form_id', $form->id)
                        ->where('email', $contactInfo['email'])
                        ->first();

                    if ($existingSubmission) {
                        throw ValidationException::withMessages([
                            'email' => ['Email ini sudah pernah mengisi form ini sebelumnya.'],
                        ]);
                    }
                }
            }

            // Calculate amounts
            $amounts = $this->calculateAmounts($form, $data);

            // Handle affiliate
            $affiliateData = $this->processAffiliate($form, $data, $amounts['total_amount']);

            // Determine payment status:
            // - If form doesn't require payment: mark as 'paid'
            // - If pricing tier amount is 0 (free): mark as 'paid'
            // - Otherwise: mark as 'pending'
            $isFree = !$form->enable_payment || $amounts['total_amount'] == 0;
            $paymentStatus = $isFree ? 'paid' : 'pending';
            $submissionStatus = $isFree ? 'approved' : 'pending';

            // Create submission
            $submission = Submission::create([
                'form_id' => $form->id,
                'data' => $formData,
                'status' => $submissionStatus,
                'payment_status' => $paymentStatus,
                'pricing_tier_id' => $data['pricing_tier_id'] ?? null,
                'tier_amount' => $amounts['base_amount'],
                'selected_upsells' => $data['upsells_selected'] ?? null,
                'upsells_amount' => $amounts['upsell_amount'] ?? 0,
                'total_amount' => $amounts['total_amount'],
                'payment_method' => $isFree ? 'free' : ($data['payment_method'] ?? null),
                'affiliate_reward_id' => $affiliateData['reward_id'],
                'affiliate_amount' => $affiliateData['commission'] ?? 0,
                'name' => $contactInfo['name'],
                'email' => $contactInfo['email'],
                'phone' => $contactInfo['phone'],
                'paid_at' => $isFree ? now() : null,
            ]);

            // Auto-create Xendit invoice for paid submissions
            if (!$isFree && $form->enable_payment) {
                $payment = $this->xenditService->createInvoice($submission, $data);
                $submission->payment_invoice_url = $payment->xendit_invoice_url;
            }

            // Auto-generate affiliate code for user if this is their first submission for this form
            if ($user) {
                $this->createAffiliateForUser($user, $form);
            }

            return $submission->load(['form', 'pricingTier', 'affiliateReward', 'payment']);
        });
    }

    private function handleFileUploads(Form $form, array $data): array
    {
        $processedData = $data;

        // Get all file fields from form
        $fileFields = $form->sections()
            ->with('fields')
            ->get()
            ->pluck('fields')
            ->flatten()
            ->where('type', 'file');

        foreach ($fileFields as $field) {
            $fieldName = $field->name;

            // Check if file exists in request
            if (request()->hasFile("data.{$fieldName}")) {
                $file = request()->file("data.{$fieldName}");

                // Store file
                $path = $file->store('submissions', 'public');

                // Save file path to data
                $processedData[$fieldName] = $path;
            }
        }

        return $processedData;
    }

    private function validateFormData(Form $form, array $data): void
    {
        $rules = [];
        $messages = [];

        foreach ($form->sections as $section) {
            foreach ($section->fields as $field) {
                $fieldRules = [];

                if ($field->required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Add type-specific validation
                switch ($field->type) {
                    case 'email':
                        $fieldRules[] = 'email';
                        break;
                    case 'phone':
                        $fieldRules[] = 'regex:/^[0-9+\-\s()]+$/';
                        break;
                    case 'number':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                    case 'file':
                        $fieldRules[] = 'file';
                        break;
                    case 'affiliate':
                        // Affiliate field validation
                        $fieldRules[] = 'string';
                        // Validate that affiliate code exists and is approved for this form
                        $fieldRules[] = function ($attribute, $value, $fail) use ($form) {
                            if (!$value) {
                                return; // Skip if empty and not required
                            }
                            $affiliate = AffiliateReward::where('affiliate_code', $value)
                                ->where('form_id', $form->id)
                                ->where('is_active', true)
                                ->where('status', 'approved')
                                ->first();

                            if (!$affiliate) {
                                $fail('Kode affiliate tidak valid atau belum disetujui untuk form ini.');
                            }
                        };
                        break;
                }

                // Add custom validation rules if defined
                if ($field->validation_rules) {
                    $fieldRules = array_merge($fieldRules, $field->validation_rules);
                }

                $rules['data.' . $field->id] = $fieldRules;
                $messages['data.' . $field->id . '.required'] = "Field '{$field->label}' is required";
            }
        }

        $validator = Validator::make(['data' => $data], $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function extractContactInfo(array $data): array
    {
        return [
            'name' => $data['name'] ?? $data['full_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? $data['phone_number'] ?? null,
        ];
    }

    private function calculateAmounts(Form $form, array $data): array
    {
        $baseAmount = 0;
        $upsellAmount = 0;

        if ($form->enable_payment) {
            // Get pricing tier amount
            if (isset($data['pricing_tier_id'])) {
                $pricingTier = PricingTier::findOrFail($data['pricing_tier_id']);
                $baseAmount = $pricingTier->price;
            }

            // Calculate upsells
            if (isset($data['upsells_selected']) && is_array($data['upsells_selected'])) {
                $upsells = Upsell::whereIn('id', $data['upsells_selected'])
                    ->where('enabled', true)
                    ->get();

                $upsellAmount = $upsells->sum('upsell_price');
            }
        }

        return [
            'base_amount' => $baseAmount,
            'upsell_amount' => $upsellAmount,
            'total_amount' => $baseAmount + $upsellAmount,
        ];
    }

    private function processAffiliate(Form $form, array $data, float $totalAmount): array
    {
        // Check if form has affiliate field
        if (!$form->hasAffiliateField()) {
            return [
                'code' => null,
                'reward_id' => null,
                'commission' => 0,
            ];
        }

        // Get affiliate code from form data
        $affiliateField = $form->getAffiliateField();
        $affiliateCode = $data['data'][$affiliateField->name] ?? null;

        // If no affiliate code provided, return null
        if (!$affiliateCode) {
            return [
                'code' => null,
                'reward_id' => null,
                'commission' => 0,
            ];
        }

        $affiliate = AffiliateReward::where('affiliate_code', $affiliateCode)
            ->where('form_id', $form->id)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->first();

        if (!$affiliate) {
            return [
                'code' => $affiliateCode,
                'reward_id' => null,
                'commission' => 0,
            ];
        }

        // Calculate commission based on type
        $commission = 0;
        if ($affiliate->commission_type === 'percentage') {
            $commission = ($totalAmount * $affiliate->commission_value) / 100;
        } else {
            $commission = $affiliate->commission_value;
        }

        // Update affiliate stats
        $affiliate->increment('total_referrals');
        $affiliate->increment('total_earned', $commission);

        return [
            'code' => $affiliateCode,
            'reward_id' => $affiliate->id,
            'commission' => $commission,
        ];
    }

    public function updatePaymentStatus(string $submissionId, string $status, array $additionalData = []): Submission
    {
        $submission = Submission::findOrFail($submissionId);

        $updateData = ['payment_status' => $status];

        if ($status === 'paid') {
            $updateData['paid_at'] = now();
        }

        if (isset($additionalData['payment_reference'])) {
            $updateData['payment_reference'] = $additionalData['payment_reference'];
        }

        $submission->update($updateData);

        return $submission;
    }

    /**
     * Auto-create affiliate code for user after successful form submission
     */
    private function createAffiliateForUser($user, Form $form): void
    {
        // Check if user already has affiliate for this form
        $existingAffiliate = \App\Models\AffiliateReward::where('user_id', $user->id)
            ->where('form_id', $form->id)
            ->first();

        if ($existingAffiliate) {
            return; // User already has affiliate for this form
        }

        // Generate unique affiliate code
        $affiliateCode = $this->generateUniqueAffiliateCode($user, $form);

        // Create affiliate reward
        \App\Models\AffiliateReward::create([
            'user_id' => $user->id,
            'form_id' => $form->id,
            'affiliate_code' => $affiliateCode,
            'commission_type' => 'percentage', // Default commission type
            'commission_value' => 10, // Default 10% commission
            'status' => 'approved', // Auto-approve for form submitters
            'is_active' => true,
            'total_earned' => 0,
            'total_referrals' => 0,
            'approved_at' => now(),
            'approved_by' => null, // System auto-approval
        ]);
    }

    /**
     * Generate unique affiliate code for user and form
     */
    private function generateUniqueAffiliateCode($user, Form $form): string
    {
        // Base code format: FORMSLUG_USERNAME_RANDOM
        $baseCode = strtoupper(
            substr($form->slug, 0, 8) . '_' .
            substr(preg_replace('/[^a-zA-Z0-9]/', '', $user->name), 0, 6) . '_' .
            substr(uniqid(), -4)
        );

        // Ensure uniqueness
        $counter = 1;
        $affiliateCode = $baseCode;

        while (\App\Models\AffiliateReward::where('affiliate_code', $affiliateCode)->exists()) {
            $affiliateCode = $baseCode . $counter;
            $counter++;
        }

        return $affiliateCode;
    }
}
