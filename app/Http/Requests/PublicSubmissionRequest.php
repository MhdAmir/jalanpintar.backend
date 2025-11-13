<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'form_slug' => 'required|string|exists:forms,slug',
            'data' => 'required|array',
            'pricing_tier_id' => 'nullable|uuid|exists:pricing_tiers,id',
            'upsells_selected' => 'nullable|array',
            'upsells_selected.*' => 'uuid|exists:upsells,id',
            'affiliate_code' => 'nullable|string|exists:affiliate_rewards,affiliate_code',
            'payment_method' => 'nullable|string|in:credit_card,bank_transfer,e_wallet,cash',
        ];
    }

    public function messages(): array
    {
        return [
            'form_slug.exists' => 'Form tidak ditemukan atau tidak aktif',
            'affiliate_code.exists' => 'Kode afiliasi tidak valid',
        ];
    }
}
