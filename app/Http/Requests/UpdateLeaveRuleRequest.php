<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaveRuleId = $this->route('id');
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
            ],
            'type' => ['required', 'string', 'in:paid,unpaid'],
            'days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}