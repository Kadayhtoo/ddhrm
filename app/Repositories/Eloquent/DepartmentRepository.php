<?php

namespace App\Repositories\Eloquent;

use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function __construct(
        protected Department $model,
    ) {}

    public function findById(int $id): ?Department
    {
        return $this->model->newQuery()->find($id);
    }

    public function allActive(): Collection
    {
        return $this->model->newQuery()->where('is_active', true)->orderBy('name')->get();
    }

    public function paginateActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function paginateDepartment(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->orderByDesc('id');

        if ($search) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

            $query->where('name', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    public function create(array $attributes): Department
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(Department $department, array $attributes): bool
    {
        return $department->update($attributes);
    }

    public function delete(Department $department): bool
    {
        return (bool) $department->delete();
    }
}
