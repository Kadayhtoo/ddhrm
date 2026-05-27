<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\AttendanceRequestRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public const OFFICE_START = '09:00:00';
    public const OFFICE_END = '18:30:00';
    public const GRACE_MINUTES = 15;
    public const MINIMUM_WORK_MINUTES = 240;

    public function __construct(
        protected AttendanceRepositoryInterface $attendances,
        protected AttendanceRequestRepositoryInterface $requests,
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
        if (!$this->canManage($actor)) {
            $filters['user_id'] = $actor->id;
        }

        $this->autoCloseOpenAttendances();

        return $this->attendances->paginate($filters, $perPage);
    }

    public function findVisible(int $id, User $actor): Attendance
    {
        $attendance = $this->attendances->findById($id);

        if (!$attendance) {
            abort(404, 'Attendance not found');
        }

        if (
            !$this->canManage($actor) &&
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

    public function createCorrectionRequest(
        User $actor,
        array $data
    ): AttendanceRequest {
        $userId = $this->canManage($actor) && !empty($data['user_id'])
            ? (int) $data['user_id']
            : $actor->id;

        $attendance = $this->attendances->findByUserAndDate(
            $userId,
            $data['requested_date']
        );

        return $this->requests->create([
            'attendance_id' => $attendance?->id,
            'user_id' => $userId,
            'requested_by' => $actor->id,
            'type' => $data['type'],
            'requested_date' => $data['requested_date'],
            'requested_clock_in_at' => $data['requested_clock_in_at'] ?? null,
            'requested_clock_out_at' => $data['requested_clock_out_at'] ?? null,
            'requested_status' => $data['requested_status'] ?? null,
            'reason' => $data['reason'],
            'status' => 'pending',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ])->load([
            'user.department',
            'attendance',
            'requestedBy',
            'reviewedBy',
        ]);
    }

    public function correctionRequests(
        User $actor,
        array $filters,
        int $perPage
    ): LengthAwarePaginator {
        if (!$this->canManage($actor)) {
            $filters['user_id'] = $actor->id;
        }

        return $this->requests->paginate($filters, $perPage);
    }

    public function reviewCorrectionRequest(
        int $id,
        string $status,
        ?string $note,
        User $actor
    ): AttendanceRequest {
        if (!$this->canManage($actor)) {
            abort(
                403,
                'You are not allowed to review attendance requests.'
            );
        }

        return DB::transaction(function () use (
            $id,
            $status,
            $note,
            $actor
        ) {
            $request = $this->requests->findById($id);

            if (!$request) {
                abort(404, 'Attendance request not found');
            }

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => [
                        'This request has already been reviewed.',
                    ],
                ]);
            }

            $updated = $this->requests->update($request, [
                'status' => $status,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'reviewer_note' => $note,
                'updated_by' => $actor->id,
            ]);

            if ($status === 'approved') {
                $this->applyCorrection($updated, $actor);
            }

            return $this->requests->findById($id);
        });
    }

    public function dailyReport(User $actor, array $filters): array
    {
        if (!$this->canManage($actor)) {
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
        if (!$this->canManage($actor)) {
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
            !$this->canManage($actor) &&
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

    public function dashboardWidgets(
        User $actor,
        ?string $date = null
    ): array {
        $filters = [
            'date' => $date ?? now()->toDateString(),
        ];

        if (!$this->canManage($actor)) {
            $filters['user_id'] = $actor->id;
        }

        $records = $this->attendances->dailyRecords(
            $filters['date'],
            $filters
        );

        $summary = $this->summaryFromRecords($records);

        return [
            'present' => $summary['present'],
            'absent' => $summary['absent'],
            'late' => $summary['late'],
            'attendance_percentage' => $summary['attendance_percentage'],
        ];
    }

    public function autoCloseOpenAttendances(): void
    {
        $today = now()->toDateString();

        foreach ($this->attendances->openBefore($today) as $attendance) {
            $checkout = Carbon::parse(
                $attendance->attendance_date->toDateString()
                . ' '
                . self::OFFICE_END
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
            . ' '
            . self::OFFICE_START
        )->addMinutes(self::GRACE_MINUTES);

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
        if ($workMinutes < self::MINIMUM_WORK_MINUTES) {
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

        if (!$attendance || !$attendance->clock_in_at) {
            throw ValidationException::withMessages([
                'attendance' => [
                    'You must check in before this action.',
                ],
            ]);
        }

        return $attendance;
    }

    protected function applyCorrection(
        AttendanceRequest $request,
        User $actor
    ): void {
        $attendance = $request->attendance
            ?: $this->attendances->create([
                'user_id' => $request->user_id,
                'attendance_date' => $request->requested_date,
                'source' => 'manual',
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

        $clockIn = $request->requested_clock_in_at
            ?? $attendance->clock_in_at;

        $clockOut = $request->requested_clock_out_at
            ?? $attendance->clock_out_at;

        $workMinutes = (
            $clockIn && $clockOut
        )
            ? max(
                0,
                Carbon::parse($clockIn)
                    ->diffInMinutes(Carbon::parse($clockOut))
            )
            : $attendance->work_minutes;

        $status = $request->requested_status
            ?: (
                $clockIn
                    ? $this->statusFromMetrics(
                        $clockIn,
                        $workMinutes
                    )
                    : 'absent'
            );

        $this->attendances->update($attendance, [
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'work_minutes' => $workMinutes,
            'late_minutes' => $clockIn
                ? $this->lateMinutes($clockIn)
                : 0,
            'status' => $status,
            'source' => 'manual',
            'updated_by' => $actor->id,
        ]);
    }

    protected function summaryFromRecords($records): array
    {
        $total = $records->count();

        $present = $records
            ->whereIn('status', ['present', 'late'])
            ->count();

        $absent = $records
            ->where('status', 'absent')
            ->count();

        $late = $records
            ->where('status', 'late')
            ->count();

        $halfDay = $records
            ->where('status', 'half_day')
            ->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'half_day' => $halfDay,
            'attendance_percentage' => $total > 0
                ? round(($present / $total) * 100, 2)
                : 0,
        ];
    }

    protected function canManage(User $user): bool
    {
        return $user->hasPermission('attendance.manage')
            || $user->hasRoleSlug('admin');
    }
}