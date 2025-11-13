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
    public function submitForm(array $data): Submission
    {
        return DB::transaction(function () use ($data) {
            // Get the form
            $form = Form::with(['sections.fields', 'pricingTiers', 'upsells'])
                ->where('slug', $data['form_slug'])
                ->where('is_active', true)
                ->firstOrFail();

            // Validate form fields
            $this->validateFormData($form, $data['data']);

            // Extract contact information from data
            $contactInfo = $this->extractContactInfo($data['data']);

            // Calculate amounts
            $amounts = $this->calculateAmounts($form, $data);

            // Handle affiliate
            $affiliateData = $this->processAffiliate($form, $data, $amounts['total_amount']);

            // Create submission
            $submission = Submission::create([
                'form_id' => $form->id,
                'data' => $data['data'],
                'payment_status' => $form->enable_payment ? 'unpaid' : 'paid',
                'pricing_tier_id' => $data['pricing_tier_id'] ?? null,
                'amount' => $amounts['base_amount'],
                'upsells_selected' => $data['upsells_selected'] ?? null,
                'total_amount' => $amounts['total_amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'affiliate_code' => $affiliateData['code'],
                'affiliate_reward_id' => $affiliateData['reward_id'],
                'affiliate_commission' => $affiliateData['commission'],
                'contact_name' => $contactInfo['name'],
                'contact_email' => $contactInfo['email'],
                'contact_phone' => $contactInfo['phone'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $submission->load(['form', 'pricingTier', 'affiliateReward']);
        });
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
        if (!$form->enable_affiliate || !isset($data['affiliate_code'])) {
            return [
                'code' => null,
                'reward_id' => null,
                'commission' => null,
            ];
        }

        $affiliate = AffiliateReward::where('affiliate_code', $data['affiliate_code'])
            ->where('form_id', $form->id)
            ->where('is_active', true)
            ->first();

        if (!$affiliate) {
            return [
                'code' => $data['affiliate_code'],
                'reward_id' => null,
                'commission' => null,
            ];
        }

        $commission = ($totalAmount * $affiliate->commission_percentage) / 100;

        // Update affiliate stats
        $affiliate->increment('total_referrals');
        $affiliate->increment('total_earned', $commission);

        return [
            'code' => $data['affiliate_code'],
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
}
