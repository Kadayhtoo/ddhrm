<?php

return [
    'daily_work_minutes' => 570,
    'late_penalty_rate' => env('PAYROLL_LATE_PENALTY_RATE', 3500),
    'paid_leave_deduction_rate' => env('PAYROLL_PAID_LEAVE_DEDUCTION_RATE', 0),
    'days_per_month' => env('PAYROLL_DAYS_PER_MONTH', 30),
];
