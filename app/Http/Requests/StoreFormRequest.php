<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:forms,slug',
            'is_active' => 'sometimes|boolean',
            'enable_payment' => 'sometimes|boolean',
            'enable_affiliate' => 'sometimes|boolean',
            'category_id' => 'nullable|uuid|exists:categories,id',

            // Sections (optional on create)
            'sections' => 'sometimes|array',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'sometimes|integer|min:0',

            // Fields within sections
            'sections.*.fields' => 'sometimes|array',
            'sections.*.fields.*.label' => 'required|string|max:255',
            'sections.*.fields.*.type' => 'required|in:text,email,phone,textarea,number,select,checkbox,radio,date,file',
            'sections.*.fields.*.required' => 'sometimes|boolean',
            'sections.*.fields.*.placeholder' => 'nullable|string',
            'sections.*.fields.*.options' => 'nullable|array',
            'sections.*.fields.*.validation_rules' => 'nullable|array',
            'sections.*.fields.*.order' => 'sometimes|integer|min:0',
        ];
    }
}
