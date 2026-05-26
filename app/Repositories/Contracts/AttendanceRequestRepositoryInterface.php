<?php

namespace App\Repositories\Contracts;

use App\Models\AttendanceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AttendanceRequestRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?AttendanceRequest;

    public function create(array $attributes): AttendanceRequest;

    public function update(AttendanceRequest $attendanceRequest, array $attributes): AttendanceRequest;
}
