<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService,
        protected StaffService $staffService,
    ) {}

    public function assignable(Request $request): JsonResponse
    {
        if (! $request->user()?->hasAnyPermission(['staff.create', 'staff.update'])) {
            abort(403);
        }

        return response()->json([
            'data' => $this->staffService->assignableRoles($request->user()),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return response()->json([
            'data' => $this->roleService->allWithPermissions(),
            'permissions' => Permission::all(),
        ]);
    }

    public function show(Request $request, Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return response()->json([
            'data' => $this->roleService->load($role),
            'permissions' => Permission::all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();

        return response()->json([
            'data' => $this->roleService->updatePermissions(
                $role,
                $validated['permission_ids'],
            ),
        ]);
    }
}
