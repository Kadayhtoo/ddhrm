<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRuleRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:leave_rules,name'],
            'type' => ['required', 'string', 'in:paid,unpaid'],
            'days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}