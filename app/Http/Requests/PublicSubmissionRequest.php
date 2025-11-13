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
            'data.*' => 'nullable', // Allow any data including files
            'pricing_tier_id' => 'nullable|uuid|exists:pricing_tiers,id',
            'upsells_selected' => 'nullable|array',
            'upsells_selected.*' => 'uuid|exists:upsells,id',
            'payment_method' => 'nullable|string|in:credit_card,bank_transfer,e_wallet,cash',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'payment_status' => 'nullable|string|in:pending,paid,failed,refunded',
        ];
    }

    public function messages(): array
    {
        return [
            'form_slug.exists' => 'Form tidak ditemukan atau tidak aktif',
        ];
    }
}
