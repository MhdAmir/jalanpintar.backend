<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffiliateRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|uuid|exists:forms,id',
            'affiliate_name' => 'required|string|max:255',
            'affiliate_code' => 'required|string|max:255|unique:affiliate_rewards,affiliate_code',
            'email' => 'nullable|email|max:255',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'payout_status' => 'sometimes|in:pending,processing,paid,cancelled',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
