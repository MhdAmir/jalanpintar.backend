<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'required|uuid|exists:sections,id',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,email,phone,textarea,number,select,checkbox,date,file',
            'required' => 'sometimes|boolean',
            'placeholder' => 'nullable|string',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'order' => 'sometimes|integer|min:0',
        ];
    }
}
