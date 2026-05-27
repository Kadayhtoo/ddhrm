<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'approver_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ((int) $value === (int) Auth::id()) {
                        $fail('You cannot select yourself as the leave request approver.');
                    }
                },
            ],
            'leave_rule_id' => 'required|exists:leave_rules,id',
            'leave_session' => 'required|in:full_day,morning,afternoon',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:0.5',
            'reason' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:2048',
        ];
    }
}
