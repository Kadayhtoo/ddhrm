<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function __construct(
        protected Attendance $model,
    ) {}

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['user.department'])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Attendance
    {
        return $this->model->newQuery()
            ->with(['user.department', 'requests'])
            ->find($id);
    }

    public function findByUserAndDate(int $userId, string $date): ?Attendance
    {
        return $this->model->newQuery()
            ->with(['user.department'])
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $date)
            ->first();
    }

    public function create(array $attributes): Attendance
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(Attendance $attendance, array $attributes): Attendance
    {
        $attendance->update($attributes);

        return $attendance->refresh()->load(['user.department']);
    }

    public function dailyRecords(string $date, array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->with(['user.department'])
            ->whereDate('attendance_date', $date)
            ->orderBy('clock_in_at');

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function monthlyRecords(int $year, int $month, array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->with(['user.department'])
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->orderBy('attendance_date')
            ->orderBy('user_id');

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function openBefore(string $date): Collection
    {
        return $this->model->newQuery()
            ->whereDate('attendance_date', '<', $date)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->get();
    }

    protected function applyFilters($query, array $filters): void
    {
        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('user', fn ($q) => $q->where('department_id', (int) $filters['department_id']));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('attendance_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('attendance_date', '<=', $filters['to']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $filters['search']).'%';
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }
    }
}
