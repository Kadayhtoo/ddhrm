<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:clock_in,clock_out,status,full_day',
            'requested_date' => 'required|date',
            'requested_clock_in_at' => 'nullable|date',
            'requested_clock_out_at' => 'nullable|date|after_or_equal:requested_clock_in_at',
            'requested_status' => 'nullable|in:present,absent,late,half_day,holiday,weekend,on_leave',
            'reason' => 'required|string|max:2000',
        ];
    }
}
