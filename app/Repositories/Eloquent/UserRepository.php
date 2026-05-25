<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model,
    ) {}

    public function findById(int $id): ?User
    {
        return $this->model->newQuery()->find($id);
    }

    public function findByIdWithRoles(int $id): ?User
    {
        return $this->model->newQuery()->with('roles.permissions')->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()
            ->with(['roles.permissions'])
            ->where('email', $email)
            ->first();
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

    public function paginateStaff(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with('roles', 'department', 'position')
            ->orderByDesc('id');

        if ($search) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                ->orWhere('email', 'like', $term);
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $attributes): User
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(User $user, array $attributes): bool
    {
        return $user->update($attributes);
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
}
