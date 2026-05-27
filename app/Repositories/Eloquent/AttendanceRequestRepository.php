<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceRequest;
use App\Repositories\Contracts\AttendanceRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceRequestRepository implements AttendanceRequestRepositoryInterface
{
    public function __construct(
        protected AttendanceRequest $model,
    ) {}

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['user.department', 'attendance', 'requestedBy', 'reviewedBy'])
            ->orderByDesc('id');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('requested_date', $filters['date']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $filters['search']).'%';
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?AttendanceRequest
    {
        return $this->model->newQuery()
            ->with(['user.department', 'attendance', 'requestedBy', 'reviewedBy'])
            ->find($id);
    }

    public function create(array $attributes): AttendanceRequest
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(AttendanceRequest $attendanceRequest, array $attributes): AttendanceRequest
    {
        $attendanceRequest->update($attributes);

        return $attendanceRequest->refresh()->load(['user.department', 'attendance', 'requestedBy', 'reviewedBy']);
    }
}
