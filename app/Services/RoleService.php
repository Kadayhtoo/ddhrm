<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function allWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function load(Role $role): Role
    {
        return $role->load('permissions');
    }

    public function updatePermissions(Role $role, array $permissionIds): Role
    {
        $role->permissions()->sync($permissionIds);

        return $role->load('permissions');
    }
}
