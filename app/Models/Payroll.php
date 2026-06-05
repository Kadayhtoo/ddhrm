<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'user_id',
        'period_type',
        'period_start',
        'period_end',
        'base_salary',
        'total_working_days',
        'total_work_minutes',
        'total_late_minutes',
        'late_penalty',
        'total_unpaid_leave_days',
        'unpaid_leave_deduction',
        'total_paid_leave_days',
        'paid_leave_deduction',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
        'calculated_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'base_salary' => 'decimal:2',
            'late_penalty' => 'decimal:2',
            'unpaid_leave_deduction' => 'decimal:2',
            'paid_leave_deduction' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'total_unpaid_leave_days' => 'decimal:2',
            'total_paid_leave_days' => 'decimal:2',
            'calculated_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
