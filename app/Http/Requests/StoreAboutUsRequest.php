<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAboutUsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'township' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone_numbers' => ['nullable', 'array'],
            'phone_numbers.*' => ['nullable', 'string', 'max:100'],
            'email_addresses' => ['nullable', 'array'],
            'email_addresses.*' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }
}
