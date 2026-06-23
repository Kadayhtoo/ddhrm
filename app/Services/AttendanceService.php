<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public const OFFICE_START = '09:00';

    public const OFFICE_END = '18:30';

    public const GRACE_MINUTES = 15;

    public const MINIMUM_WORK_MINUTES = 240;

    protected static function settingValue(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        return Setting::getValue($key, $default);
    }

    protected static function officeStart(): string
    {
        return (string) self::settingValue(
            'attendance.office_start',
            config('attendance.office_start', self::OFFICE_START)
        );
    }

    protected static function officeEnd(): string
    {
        return (string) self::settingValue(
            'attendance.office_end',
            config('attendance.office_end', self::OFFICE_END)
        );
    }

    protected static function graceMinutes(): int
    {
        return (int) self::settingValue(
            'attendance.grace_minutes',
            config('attendance.grace_minutes', self::GRACE_MINUTES)
        );
    }

    protected static function minimumWorkMinutes(): int
    {
        return (int) self::settingValue(
            'attendance.minimum_work_minutes',
            config('attendance.minimum_work_minutes', self::MINIMUM_WORK_MINUTES)
        );
    }

    public function __construct(
        protected AttendanceRepositoryInterface $attendances,
    ) {}

    public function today(User $actor): ?Attendance
    {
        return $this->attendances->findByUserAndDate(
            $actor->id,
            now()->toDateString()
        );
    }

    public function history(
        User $actor,
        array $filters,
        int $perPage
    ): LengthAwarePaginator {
        if (! $this->canManage($actor)) {
            $filters['user_id'] = $actor->id;
        }

        $this->autoCloseOpenAttendances();

        return $this->attendances->paginate($filters, $perPage);
    }

    public function findVisible(int $id, User $actor): Attendance
    {
        $attendance = $this->attendances->findById($id);

        if (! $attendance) {
            abort(404, 'Attendance not found');
        }

        if (
            ! $this->canManage($actor) &&
            (int) $attendance->user_id !== (int) $actor->id
        ) {
            abort(
                403,
                'You are not allowed to view this attendance record.'
            );
        }

        return $attendance;
    }

    public function checkIn(User $actor, ?string $notes = null): Attendance
    {
        return DB::transaction(function () use ($actor, $notes) {
            $now = now();
            $date = $now->toDateString();

            $attendance = $this->attendances->findByUserAndDate(
                $actor->id,
                $date
            );

            if ($attendance && $attendance->clock_in_at) {
                throw ValidationException::withMessages([
                    'attendance' => [
                        'You have already checked in today.',
                    ],
                ]);
            }

            $payload = [
                'user_id' => $actor->id,
                'attendance_date' => $date,
                'clock_in_at' => $now,
                'late_minutes' => $this->lateMinutes($now),
                'status' => $this->statusFromClockIn($now),
                'source' => 'web',
                'notes' => $notes,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ];

            if ($attendance) {
                return $this->attendances->update($attendance, $payload);
            }

            return $this->attendances
                ->create($payload)
                ->load(['user.department']);
        });
    }

    public function checkOut(User $actor, ?string $notes = null): Attendance
    {
        return DB::transaction(function () use ($actor, $notes) {
            $attendance = $this->requireTodayAttendance($actor);

            if ($attendance->clock_out_at) {
                throw ValidationException::withMessages([
                    'attendance' => [
                        'You have already checked out today.',
                    ],
                ]);
            }

            $now = now();

            $workMinutes = max(
                0,
                Carbon::parse($attendance->clock_in_at)
                    ->diffInMinutes($now)
            );

            return $this->attendances->update($attendance, [
                'clock_out_at' => $now,
                'work_minutes' => $workMinutes,
                'late_minutes' => $this->lateMinutes(
                    $attendance->clock_in_at
                ),
                'status' => $this->statusFromMetrics(
                    $attendance->clock_in_at,
                    $workMinutes
                ),
                'notes' => $notes ?? $attendance->notes,
                'updated_by' => $actor->id,
            ]);
        });
    }

    public function dailyReport(User $actor, array $filters): array
    {
        if (! $this->canManage($actor)) {
            $filters['user_id'] = $actor->id;
        }

        $date = $filters['date'] ?? now()->toDateString();

        $records = $this->attendances->dailyRecords($date, $filters);

        return [
            'date' => $date,
            'summary' => $this->summaryFromRecords($records),
            'records' => $records,
        ];
    }

    public function monthlyReport(User $actor, array $filters): array
    {
        if (! $this->canManage($actor)) {
            $filters['user_id'] = $actor->id;
        }

        $year = (int) ($filters['year'] ?? now()->year);
        $month = (int) ($filters['month'] ?? now()->month);

        $records = $this->attendances->monthlyRecords(
            $year,
            $month,
            $filters
        );

        return [
            'year' => $year,
            'month' => $month,
            'summary' => $this->summaryFromRecords($records),
            'records' => $records,
        ];
    }

    public function employeeSummary(
        User $actor,
        int $userId,
        array $filters
    ): array {
        if (
            ! $this->canManage($actor) &&
            (int) $actor->id !== $userId
        ) {
            abort(
                403,
                'You are not allowed to view this summary.'
            );
        }

        $filters['user_id'] = $userId;

        $year = (int) ($filters['year'] ?? now()->year);
        $month = (int) ($filters['month'] ?? now()->month);

        $records = $this->attendances->monthlyRecords(
            $year,
            $month,
            $filters
        );

        return [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
            'summary' => $this->summaryFromRecords($records),
        ];
    }

    // public function dashboardWidgets(User $actor,?string $date = null): array
    // {
    //     $filters = [
    //         'date' => $date ?? now()->toDateString(),
    //     ];
    //     if (! $this->canManage($actor)) {
    //         $filters['user_id'] = $actor->id;
    //     }
    //     $records = $this->attendances->dailyRecords(
    //         $filters['date'],
    //         $filters
    //     );
    //     $summary = $this->summaryFromRecords($records);
    //     return [
    //         'present' => $summary['present'],
    //         'absent' => $summary['absent'],
    //         'late' => $summary['late'],
    //         'attendance_percentage' => $summary['attendance_percentage'],
    //     ];
    // }

    public function dashboardWidgets(User $actor, ?string $date = null): array
    {
        if ($this->canManage($actor)) {
            $targetDate = $date ?? now()->toDateString();

            $records = $this->attendances->dailyRecords(
                $targetDate,
                ['date' => $targetDate]
            );
        } else {
            $records = $this->attendances->rangeRecords(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
                ['user_id' => $actor->id]
            );
        }

        $summary = $this->summaryFromRecords($records);

        return [
            'present' => $summary['present'] ?? 0,
            'absent' => $summary['absent'] ?? 0,
            'late' => $summary['late'] ?? 0,
            'attendance_percentage' => $summary['attendance_percentage'] ?? 0,
        ];
    }

    public function autoCloseOpenAttendances(): void
    {
        $today = now()->toDateString();

        foreach ($this->attendances->openBefore($today) as $attendance) {
            // $checkout = Carbon::parse(
            //     $attendance->attendance_date->toDateString()
            //     .' '
            //     .self::officeEnd()
            // );
            $checkout = Carbon::createFromFormat('Y-m-d H:i', 
                $attendance->attendance_date->toDateString() . ' ' . self::officeEnd()
            );
            $workMinutes = max(
                0,
                Carbon::parse($attendance->clock_in_at)
                    ->diffInMinutes($checkout)
            );

            $this->attendances->update($attendance, [
                'clock_out_at' => $checkout,
                'work_minutes' => $workMinutes,
                'late_minutes' => $this->lateMinutes(
                    $attendance->clock_in_at
                ),
                'status' => $this->statusFromMetrics(
                    $attendance->clock_in_at,
                    $workMinutes
                ),
                'is_auto_closed' => true,
                'source' => 'system',
            ]);
        }
    }

    public function lateMinutes(Carbon|string $clockIn): int
    {
        $clockIn = Carbon::parse($clockIn);

        $limit = Carbon::parse(
            $clockIn->toDateString()
            .' '
            .self::officeStart()
        )->addMinutes(self::graceMinutes());

        return $clockIn->greaterThan($limit)
            ? $limit->diffInMinutes($clockIn)
            : 0;
    }

    public function statusFromClockIn(Carbon|string $clockIn): string
    {
        return $this->lateMinutes($clockIn) > 0
            ? 'late'
            : 'present';
    }

    public function statusFromMetrics(
        Carbon|string $clockIn,
        int $workMinutes
    ): string {
        if ($workMinutes < self::minimumWorkMinutes()) {
            return 'half_day';
        }

        return $this->statusFromClockIn($clockIn);
    }

    protected function requireTodayAttendance(User $actor): Attendance
    {
        $attendance = $this->attendances->findByUserAndDate(
            $actor->id,
            now()->toDateString()
        );

        if (! $attendance || ! $attendance->clock_in_at) {
            throw ValidationException::withMessages([
                'attendance' => [
                    'You must check in before this action.',
                ],
            ]);
        }

        return $attendance;
    }

    // protected function summaryFromRecords($records): array
    // {
    //     $total = $records->count();

    //     $present = $records
    //         ->whereIn('status', ['present', 'late'])
    //         ->count();

    //     $absent = $records
    //         ->where('status', 'absent')
    //         ->count();

    //     $late = $records
    //         ->where('status', 'late')
    //         ->count();

    //     $halfDay = $records
    //         ->where('status', 'half_day')
    //         ->count();

    //     return [
    //         'total' => $total,
    //         'present' => $present,
    //         'absent' => $absent,
    //         'late' => $late,
    //         'half_day' => $halfDay,
    //         'attendance_percentage' => $total > 0
    //             ? round(($present / $total) * 100, 2)
    //             : 0,
    //     ];
    // }

    protected function summaryFromRecords($records): array
    {
        $total = $records->count();
        $present = $records->whereIn('status', ['present', 'late'])->count();
        $absent  = $records->where('status', 'absent')->count();
        $late    = $records->where('status', 'late')->count();

        return [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'attendance_percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }

    protected function canManage(User $user): bool
    {
        return $user->hasPermission('attendance.manage')
            || $user->hasRoleSlug('admin');
    }
}
