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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'sometimes|required|string|max:255|unique:forms,slug,' . $formId,
            'category_id' => 'nullable|uuid|exists:categories,id',
            'cover_image' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'enable_payment' => 'sometimes|boolean',
            'enable_affiliate' => 'sometimes|boolean',
            'max_submissions' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'settings' => 'nullable|array',

            // Sections (optional on update)
            'sections' => 'sometimes|array',
            'sections.*.id' => 'sometimes|uuid|exists:sections,id',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'sometimes|integer|min:0',

            // Fields within sections
            'sections.*.fields' => 'sometimes|array',
            'sections.*.fields.*.id' => 'sometimes|uuid|exists:fields,id',
            'sections.*.fields.*.label' => 'required|string|max:255',
            'sections.*.fields.*.name' => 'nullable|string|max:255',
            'sections.*.fields.*.type' => 'required|in:text,email,phone,textarea,number,select,checkbox,date,file',
            'sections.*.fields.*.placeholder' => 'nullable|string',
            'sections.*.fields.*.help_text' => 'nullable|string',
            'sections.*.fields.*.is_required' => 'sometimes|boolean',
            'sections.*.fields.*.required' => 'sometimes|boolean',
            'sections.*.fields.*.options' => 'nullable|array',
            'sections.*.fields.*.validation_rules' => 'nullable|array',
            'sections.*.fields.*.order' => 'sometimes|integer|min:0',

            // Pricing Tiers (optional)
            'pricing_tiers' => 'sometimes|array',
            'pricing_tiers.*.id' => 'sometimes|uuid|exists:pricing_tiers,id',
            'pricing_tiers.*.name' => 'required|string|max:255',
            'pricing_tiers.*.description' => 'nullable|string',
            'pricing_tiers.*.price' => 'required|numeric|min:0',
            'pricing_tiers.*.currency' => 'sometimes|string|max:3',
            'pricing_tiers.*.is_default' => 'sometimes|boolean',
            'pricing_tiers.*.is_active' => 'sometimes|boolean',
            'pricing_tiers.*.order' => 'sometimes|integer|min:0',

            // Upsells (optional)
            'upsells' => 'sometimes|array',
            'upsells.*.id' => 'sometimes|uuid|exists:upsells,id',
            'upsells.*.name' => 'required|string|max:255',
            'upsells.*.description' => 'nullable|string',
            'upsells.*.price' => 'required|numeric|min:0',
            'upsells.*.is_active' => 'sometimes|boolean',
            'upsells.*.order' => 'sometimes|integer|min:0',
        ];
    }
}
