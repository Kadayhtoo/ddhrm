<?php

namespace App\Services;

use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function __construct(
        protected DepartmentRepositoryInterface $departments,
    ) {}

    public function paginate(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->departments->paginateDepartment($perPage, $search);
    }

   public function create(array $data): Department
    {
        return $this->departments->create([
            'name' => $data['name'],
        ]);
    }

    public function update(Department $department, array $data): Department
    {
        $this->departments->update($department, [
            'name' => $data['name'],
        ]);

        return $this->departments->findById($department->id) ?? $department;

    }

    public function delete(Department $department): void
    {
        $this->departments->delete($department);
    }
}