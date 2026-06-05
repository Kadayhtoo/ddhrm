<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'period_type' => $this->period_type,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'base_salary' => $this->base_salary,
            'total_working_days' => $this->total_working_days,
            'total_work_minutes' => $this->total_work_minutes,
            'total_late_minutes' => $this->total_late_minutes,
            'late_penalty' => $this->late_penalty,
            'total_unpaid_leave_days' => $this->total_unpaid_leave_days,
            'unpaid_leave_deduction' => $this->unpaid_leave_deduction,
            'total_paid_leave_days' => $this->total_paid_leave_days,
            'paid_leave_deduction' => $this->paid_leave_deduction,
            'gross_salary' => $this->gross_salary,
            'total_deductions' => $this->total_deductions,
            'net_salary' => $this->net_salary,
            'status' => $this->status,
            'calculated_at' => $this->calculated_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'notes' => $this->notes,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'department_id' => $this->user?->department_id,
                'department' => $this->user?->department ? [
                    'id' => $this->user->department->id,
                    'name' => $this->user->department->name,
                ] : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
