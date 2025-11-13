<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formId = $this->route('form');

        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'sometimes|required|string|max:255|unique:forms,slug,' . $formId,
            'is_active' => 'sometimes|boolean',
            'enable_payment' => 'sometimes|boolean',
            'enable_affiliate' => 'sometimes|boolean',
            'category_id' => 'nullable|uuid|exists:categories,id',
        ];
    }
}
