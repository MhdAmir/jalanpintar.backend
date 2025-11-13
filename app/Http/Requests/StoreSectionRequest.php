<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|uuid|exists:forms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|integer|min:0',
        ];
    }
}
