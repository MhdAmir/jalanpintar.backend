<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpsellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|uuid|exists:forms,id',
            'enabled' => 'sometimes|boolean',
            'upsell_title' => 'required|string|max:255',
            'upsell_price' => 'required|numeric|min:0',
            'upsell_description' => 'nullable|string',
            'button_text' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer|min:0',
        ];
    }
}
