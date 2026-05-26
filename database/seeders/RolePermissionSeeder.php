<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['name' => 'View dashboard', 'slug' => 'dashboard.view'],
            ['name' => 'Admin panel', 'slug' => 'admin.access'],
            ['name' => 'View staff', 'slug' => 'staff.view'],
            ['name' => 'Create staff', 'slug' => 'staff.create'],
            ['name' => 'Update staff', 'slug' => 'staff.update'],
            ['name' => 'Delete staff', 'slug' => 'staff.delete'],
            ['name' => 'Roles list', 'slug' => 'roles.view'],
            ['name' => 'Manage roles', 'slug' => 'roles.manage'],
            ['name' => 'View departments', 'slug' => 'departments.view'],
            ['name' => 'Create department', 'slug' => 'departments.create'],
            ['name' => 'Update department', 'slug' => 'departments.update'],
            ['name' => 'Delete department', 'slug' => 'departments.delete'],
            ['name' => 'View positions', 'slug' => 'positions.view'],
            ['name' => 'Create position', 'slug' => 'positions.create'],
            ['name' => 'Update position', 'slug' => 'positions.update'],
            ['name' => 'Delete position', 'slug' => 'positions.delete'],
            ['name' => 'View attendance', 'slug' => 'attendance.view'],
            ['name' => 'Manage attendance', 'slug' => 'attendance.manage'],  
            ['name' => 'View leave rules', 'slug' => 'leave-rules.view'],
            ['name' => 'Ceate leave rules', 'slug' => 'leave-rules.create'],
            ['name' => 'Update leave rules', 'slug' => 'leave-rules.update'],
            ['name' => 'Delete leave rules', 'slug' => 'leave-rules.delete'],
            ['name' => 'View leave requests', 'slug' => 'leave-requests.view'],
            ['name' => 'Manage leave requests', 'slug' => 'leave-requests.manage'],
            ['name' => 'View leave balances', 'slug' => 'leave-balances.view'],
            ['name' => 'Manage leave balances', 'slug' => 'leave-balances.manage'],
        ];

        $permissions = collect($definitions)->map(fn (array $row) => Permission::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        ));

        $bySlug = $permissions->keyBy('slug');

        $roleConfigs = [
            ['name' => 'Staff', 'slug' => 'staff', 'permissions' => ['dashboard.view', 'attendance.view']],
            ['name' => 'HR', 'slug' => 'hr', 'permissions' => ['dashboard.view', 'staff.view', 'staff.create', 'staff.update', 'attendance.view', 'attendance.manage']],
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => [
                'dashboard.view', 'admin.access', 'staff.view', 'staff.create', 'staff.update', 'staff.delete', 'roles.view', 'roles.manage',
                'attendance.view', 'attendance.manage',
            ]],
            ['name' => 'CEO', 'slug' => 'ceo', 'permissions' => [
                'dashboard.view', 'admin.access', 'staff.view', 'staff.create', 'staff.update', 'staff.delete', 'roles.view', 'roles.manage',
                'attendance.view', 'attendance.manage',
            ]],
        ];

        $currentSlugs = collect($definitions)->pluck('slug')->all();
        Permission::query()->whereNotIn('slug', $currentSlugs)->delete();

        foreach ($roleConfigs as $cfg) {
            $role = Role::query()->firstOrCreate(
                ['slug' => $cfg['slug']],
                ['name' => $cfg['name']],
            );

            $targetPermissionIds = collect($cfg['permissions'])
                ->map(fn (string $slug) => $bySlug[$slug]->id ?? null)
                ->filter()
                ->all();

            if ($role->wasRecentlyCreated) {
                $role->permissions()->sync($targetPermissionIds);
            } else {
                $role->permissions()->syncWithoutDetaching($targetPermissionIds);

                $removedPermissionIds = Permission::query()
                    ->whereNotIn('slug', $currentSlugs)
                    ->pluck('id')
                    ->all();
                
                if (!empty($removedPermissionIds)) {
                    $role->permissions()->detach($removedPermissionIds);
                }
            }
        }
    }
}
