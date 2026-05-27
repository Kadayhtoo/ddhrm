<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRule;
use App\Repositories\Contracts\LeaveRuleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveRuleRepository implements LeaveRuleRepositoryInterface
{
    public function __construct(
        protected LeaveRule $model,
    ) {}

    public function findById(int $id): ?LeaveRule
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->orderByDesc('id');

        if ($search) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where('name', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    public function create(array $attributes): LeaveRule
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(mixed $leaveRule, array $attributes): bool
    {
        return $leaveRule->update($attributes);
    }

    public function delete(mixed $leaveRule): bool
    {
        return (bool) $leaveRule->delete();
    }
}
