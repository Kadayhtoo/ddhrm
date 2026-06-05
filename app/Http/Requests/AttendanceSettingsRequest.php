<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AttendanceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'office_start' => ['required', 'date_format:H:i'],
            'office_end' => ['required', 'date_format:H:i'],
            'grace_minutes' => ['required', 'integer', 'min:0'],
            'minimum_work_minutes' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('office_start') && $this->filled('office_end')) {
                try {
                    $start = Carbon::createFromFormat('H:i', $this->input('office_start'));
                    $end = Carbon::createFromFormat('H:i', $this->input('office_end'));

                    if ($start->gte($end)) {
                        $validator->errors()->add(
                            'office_end',
                            'The office end time must be after the office start time.'
                        );
                    }
                } catch (\Throwable $e) {
                    // Validation rules already cover invalid time format.
                }
            }
        });
    }
}
