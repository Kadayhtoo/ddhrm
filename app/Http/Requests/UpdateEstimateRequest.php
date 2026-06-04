<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $estimateId = $this->route('estimate_id');

       return [
            'estimate_id'     => [
                'required', 
                'string', 
                Rule::unique('estimates', 'estimate_id')->ignore($estimateId, 'estimate_id')
            ],
            'client_id'      => ['required', 'exists:clients,id'],
            'issue_date'     => ['required', 'date'],
            'due_date'       => ['required', 'date', 'after_or_equal:issue_date'],
            'currency'       => ['required', 'string', 'max:10'],
            'status'         => ['required', 'in:open,sent,accepted,rejected,cancelled'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.name'     => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price'    => ['required', 'numeric', 'min:0'],
            'items.*.item_type' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
