<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class PayrollService
{
    public const DAILY_WORK_MINUTES = 570;

    public const LATE_PENALTY_RATE = 3500;

    public const PAID_LEAVE_DEDUCTION_RATE = 0;

    public const DAYS_PER_MONTH = 30;

    protected static function settingValue(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        return Setting::getValue($key, $default);
    }

    public function dailyWorkMinutes(): int
    {
        return (int) self::settingValue(
            'payroll.daily_work_minutes',
            config('payroll.daily_work_minutes', self::DAILY_WORK_MINUTES)
        );
    }

    public function latePenaltyRate(): float
    {
        return (float) self::settingValue(
            'payroll.late_penalty_rate',
            config('payroll.late_penalty_rate', self::LATE_PENALTY_RATE)
        );
    }

    public function paidLeaveDeductionRate(): float
    {
        return (float) self::settingValue(
            'payroll.paid_leave_deduction_rate',
            config('payroll.paid_leave_deduction_rate', self::PAID_LEAVE_DEDUCTION_RATE)
        );
    }

    public function daysPerMonth(): int
    {
        return (int) self::settingValue(
            'payroll.days_per_month',
            config('payroll.days_per_month', self::DAYS_PER_MONTH)
        );
    }

    public function list(array $filters, int $perPage = 15)
    {
        $query = Payroll::query()
            ->with(['user.department'])
            ->orderByDesc('created_at');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['period_type'])) {
            $query->where('period_type', $filters['period_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['year'])) {
            $query->whereYear('period_start', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('period_start', (int) $filters['month']);
        }

        return $query->paginate($perPage);
    }

    public function stats(?User $actor = null): array
    {
        $query = Payroll::query()->where('status', 'calculated');
        $totalPayroll = (clone $query)->sum('net_salary');
        $totalDeductions = (clone $query)->sum('total_deductions');
        $totalLatePenalties = (clone $query)->sum('late_penalty');
        $totalLeaveDeductions = (clone $query)->sum(DB::raw('unpaid_leave_deduction + paid_leave_deduction'));
        $count = (clone $query)->count();
   
        $pendingCount = Payroll::query()->where('status', 'draft')->count();
        return [
            'total_payroll' => round($totalPayroll, 2),
            'total_deductions' => round($totalDeductions, 2),
            'total_late_penalties' => round($totalLatePenalties, 2),
            'total_leave_deductions' => round($totalLeaveDeductions, 2),
            'calculated_count' => $count,
            'pending_count' => $pendingCount,
        ];
    }

    /**
     * Calculate monthly payroll for a employee by monthly.
     * @return Payroll
     * @author HHA
     * @date 01/06/2026
     */
    public function calculateMonthly(int $userId, int $year, int $month): Payroll
    {
        $user = User::query()->findOrFail($userId);
        $salary = (float) ($user->salary ?? 0);

        $periodStart = Carbon::create($year, $month, 1)->startOfDay();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        return DB::transaction(function () use ($user, $salary, $periodStart, $periodEnd, $year, $month) {
            $attendances = Attendance::query()
                ->where('user_id', $user->id)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->get();

            $totalLateMinutes = (int) $attendances->sum('late_minutes');
            $totalWorkMinutes = (int) $attendances->sum('work_minutes');
            $totalWorkingDays = $attendances->count();

            $approvedLeaves = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereBetween('start_date', [$periodStart, $periodEnd])
                ->with('leaveRule')
                ->get();

            $totalUnpaidDays = 0;
            $totalPaidDays = 0;

            foreach ($approvedLeaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);
                $days = $leave->total_days;

                if ($leave->leaveRule && $leave->leaveRule->type === 'unpaid') {
                    $totalUnpaidDays += (float) $days;
                } else {
                    $totalPaidDays += (float) $days;
                }
            }

            // Daily rate = salary / daysPerMonth (reference)
            $dailyRate = $salary / max($this->daysPerMonth(), 1);

            // Minute rate = dailyRate / dailyWorkMinutes (kept for reference)
            $minuteRate = $dailyRate / max($this->dailyWorkMinutes(), 1);

            // Late penalty = (late minutes / 30) * rate (MMK per 30 min)
            $latePenalty = round(($totalLateMinutes / 30) * $this->latePenaltyRate(), 2);

            $unpaidDeduction = round($dailyRate * $totalUnpaidDays, 2);
            $paidDeduction = round($dailyRate * $totalPaidDays * $this->paidLeaveDeductionRate(), 2);

            // Total deductions = late penalty + unpaid deduction + paid deduction
            $deductions = round($latePenalty + $unpaidDeduction + $paidDeduction, 2);
            // e.g., $gross = $salary + $allowances - $otherEarnings (for future use)
            $gross = $salary;
            // Net salary = gross - deductions
            $net = round($gross - $deductions, 2);

            $attributes = [
                'user_id' => $user->id,
                'period_type' => 'monthly',
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'base_salary' => $salary,
                'total_working_days' => $totalWorkingDays,
                'total_work_minutes' => $totalWorkMinutes,
                'total_late_minutes' => $totalLateMinutes,
                'late_penalty' => $latePenalty,
                'total_unpaid_leave_days' => $totalUnpaidDays,
                'unpaid_leave_deduction' => $unpaidDeduction,
                'total_paid_leave_days' => $totalPaidDays,
                'paid_leave_deduction' => $paidDeduction,
                'gross_salary' => $gross,
                'total_deductions' => $deductions,
                'net_salary' => $net,
                'status' => 'calculated',
                'calculated_at' => now(),
            ];

            return Payroll::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'period_type' => 'monthly',
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ],
                $attributes
            );
        });
    }

    /**
     * Calculate daily payroll for a employee by daily.
     * @return Payroll
     * @author HHA
     * @date 01/06/2026
     */
    public function calculateDaily(int $userId, string $date): Payroll
    {
        $user = User::query()->findOrFail($userId);
        // $salary = (float) ($user->salary ?? 0);
        $salary = (float) 50000; // for test

        $day = Carbon::parse($date);
        $periodStart = $day->copy()->startOfDay();
        $periodEnd = $day->copy()->endOfDay();

        return DB::transaction(function () use ($user, $salary, $day, $periodStart, $periodEnd, $date) {
            $attendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('attendance_date', $date)
                ->first();

            $lateMinutes = $attendance ? (int) $attendance->late_minutes : 0;
            $workMinutes = $attendance ? (int) $attendance->work_minutes : 0;
            $workingDays = $attendance ? 1 : 0;

            $approvedLeave = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->with('leaveRule')
                ->first();

            $unpaidDays = 0;
            $paidDays = 0;

            if ($approvedLeave) {
                $days = (float) $approvedLeave->total_days;
                if ($approvedLeave->leaveRule && $approvedLeave->leaveRule->type === 'unpaid') {
                    $unpaidDays += $days;
                } else {
                    $paidDays += $days;
                }
            }

            // Daily rate = salary / daysPerMonth (reference)
            $dailyRate = $salary / max($this->daysPerMonth(), 1);
            $minuteRate = $dailyRate / max($this->dailyWorkMinutes(), 1);

            // Late penalty = (late minutes / 30) * rate (MMK per 30 min)
            $latePenalty = round(($lateMinutes / 30) * $this->latePenaltyRate(), 2);

            $unpaidDeduction = round($dailyRate * $unpaidDays, 2);
            $paidDeduction = round($dailyRate * $paidDays * $this->paidLeaveDeductionRate(), 2);

            // Total deductions = late penalty + unpaid deduction + paid deduction
            $deductions = round($latePenalty + $unpaidDeduction + $paidDeduction, 2);

            // e.g., $gross = $salary + $allowances - $otherEarnings (for future use)
            $gross = $salary;

            // Net salary = gross - deductions
            $net = round($gross - $deductions, 2);

            $attributes = [
                'user_id' => $user->id,
                'period_type' => 'daily',
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'base_salary' => $salary,
                'total_working_days' => $workingDays,
                'total_work_minutes' => $workMinutes,
                'total_late_minutes' => $lateMinutes,
                'late_penalty' => $latePenalty,
                'total_unpaid_leave_days' => $unpaidDays,
                'unpaid_leave_deduction' => $unpaidDeduction,
                'total_paid_leave_days' => $paidDays,
                'paid_leave_deduction' => $paidDeduction,
                'gross_salary' => $gross,
                'total_deductions' => $deductions,
                'net_salary' => $net,
                'status' => 'calculated',
                'calculated_at' => now(),
            ];

            return Payroll::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'period_type' => 'daily',
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ],
                $attributes
            );
        });
    }

    public function markAsPaid(int $id): Payroll
    {
        $payroll = Payroll::query()->findOrFail($id);
        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $payroll->fresh()->load('user.department');
    }

    public function show(int $id): Payroll
    {
        return Payroll::query()
            ->with('user.department')
            ->findOrFail($id);
    }
}
