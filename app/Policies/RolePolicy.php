<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRoleSlug('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['roles.view', 'roles.manage']);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasAnyPermission(['roles.view', 'roles.manage']);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasAnyPermission(['roles.manage']);
    }
}
