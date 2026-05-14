<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
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
}
