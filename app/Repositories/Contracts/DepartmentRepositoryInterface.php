<?php

namespace App\Repositories\Contracts;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface
{
    public function findById(int $id): ?Department;

    /**
     * @return Collection<int, User>
     */
    public function allActive(): Collection;

    public function paginateActive(int $perPage = 15): LengthAwarePaginator;

    public function paginateDepartment(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    public function create(array $attributes): Department;

    public function update(Department $department, array $attributes): bool;

    public function delete(Department $department): bool;
}
