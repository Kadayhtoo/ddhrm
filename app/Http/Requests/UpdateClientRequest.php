<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       $clientId = $this->route('client') ?? $this->route('id') ?? $this->id; 

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email,' . $clientId],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'township' => ['nullable', 'string', 'max:255'],
            'social_links' => ['sometimes', 'array'],
            'social_links.facebook'  => ['nullable', 'url'],
            'social_links.instagram' => ['nullable', 'url'],
            'social_links.linkedin'  => ['nullable', 'url'],
            'social_links.tiktok'    => ['nullable', 'url'],
            'social_links.telegram'  => ['nullable', 'url'],
            'social_links.viber'     => ['nullable', 'url'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
