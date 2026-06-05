<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?Attendance;

    public function findByUserAndDate(int $userId, string $date): ?Attendance;

    public function create(array $attributes): Attendance;

    public function update(Attendance $attendance, array $attributes): Attendance;

    public function dailyRecords(string $date, array $filters = []): Collection;

    public function monthlyRecords(int $year, int $month, array $filters = []): Collection;

    public function openBefore(string $date): Collection;
}
