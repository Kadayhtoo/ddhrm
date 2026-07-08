<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByIdWithRoles(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    /**
     * @return Collection<int, User>
     */
    public function allActive(): Collection;

    public function paginateActive(int $perPage = 15): LengthAwarePaginator;

    public function paginateStaff(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    public function create(array $attributes): User;

    public function update(User $user, array $attributes): bool;

    public function delete(User $user): bool;
}
