<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|uuid|exists:forms,id',
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'File harus berformat CSV',
            'file.max' => 'Ukuran file maksimal 10MB',
        ];
    }
}
