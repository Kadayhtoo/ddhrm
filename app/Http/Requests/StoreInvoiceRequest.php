<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'string', 'unique:invoices,invoice_id'],
            'client_id' => ['required', 'exists:clients,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', 'string', 'max:10'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'discount_value' => [
                'required',
                'numeric',
                'min:0', 
                function ($attribute, $value, $fail) {
                    if ($this->discount_type === 'percentage') {
                        if ($value < 1 || $value > 100) {
                            $fail('The discount percentage must be between 1 and 100.');
                        }
                    } 
                },
            ],        
            'status' => ['required', 'in:open,sent,paid,cancelled'],
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