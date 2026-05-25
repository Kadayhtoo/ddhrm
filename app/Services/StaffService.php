<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffService
{
    public function __construct(
        protected UserRepositoryInterface $users,
    ) {}

    public function paginate(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->users->paginateStaff($perPage, $search);
    }

    /**
     * @return list<array{id:int,name:string,slug:string}>
     */
    public function assignableRoles(User $actor): array
    {
        $query = Role::query()->orderBy('name');

        if (! $actor->hasPermission('roles.manage')) {
            $query->where('slug', 'staff');
        }

        return $query->get(['id', 'name', 'slug'])
            ->map(fn (Role $r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug])
            ->values()
            ->all();
    }

    public function create(array $data, User $actor): User
    {
        $roleId = (int) $data['role_id'];
        $this->assertRoleAssignable($actor, $roleId);
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'department_id' => $data['department_id'] ?? null,
            'position_id'   => $data['position_id'] ?? null,
            'salary' => $data['salary'] ?? null,
            'shift_id' => $data['shift_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $user->roles()->sync([$roleId]);

        return $this->users->findByIdWithRoles($user->id) ?? $user;
    }

    public function update(User $target, array $data, User $actor): User
    {
        $payload = [
            'name' => $data['name'] ?? $target->name,
            'email' => $data['email'] ?? $target->email,
            'department_id' => array_key_exists('department_id', $data) ? $data['department_id'] : $target->department_id,
            'position_id'   => array_key_exists('position_id', $data) ? $data['position_id'] : $target->position_id,
            'salary' => array_key_exists('salary', $data) ? $data['salary'] : $target->salary,
            'shift_id' => array_key_exists('shift_id', $data) ? $data['shift_id'] : $target->shift_id,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $target->is_active,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $this->users->update($target, $payload);

        $target->refresh();

        if (isset($data['role_id'])) {
            $roleId = (int) $data['role_id'];
            $this->assertRoleAssignable($actor, $roleId);
            $target->roles()->sync([$roleId]);
        }

        $target->refresh();

        return $this->users->findByIdWithRoles($target->id) ?? $target;
    }

    public function delete(User $target, User $actor): void
    {
        if ($actor->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => [__('You cannot delete your own account.')],
            ]);
        }

        $this->users->delete($target);
    }

    protected function assertRoleAssignable(User $actor, int $roleId): void
    {
        $role = Role::query()->find($roleId);
        if (! $role) {
            throw ValidationException::withMessages([
                'role_id' => [__('Invalid role.')],
            ]);
        }

        if ($actor->hasPermission('roles.manage')) {
            return;
        }

        if ($role->slug !== 'staff') {
            throw ValidationException::withMessages([
                'role_id' => [__('You may only assign the Staff role.')],
            ]);
        }
    }
}
