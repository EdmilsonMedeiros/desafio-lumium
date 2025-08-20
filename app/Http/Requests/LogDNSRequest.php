<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogDNSRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dns_file' => 'required|file|mimes:csv,txt',
        ];
    }

    public function messages(): array
    {
        return [
            'dns_file.required' => 'O arquivo CSV é obrigatório.',
            'dns_file.file' => 'O arquivo deve ser um CSV.',
            'dns_file.mimes' => 'O arquivo deve ser um CSV.',
        ];
    }
}
