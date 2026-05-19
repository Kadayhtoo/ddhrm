<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\User;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService,
    ){}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $search = $request->query('search');

        return DepartmentResource::collection(
            $this->departmentService->paginate($perPage, is_string($search) ? $search : null)
        );
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->create($request->validated());

        return (new DepartmentResource($department))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Department $department): DepartmentResource
    {
        $this->authorize('view', $department);

        return new DepartmentResource($department);
    }

    public function update(Request $request, Department $department): DepartmentResource
    {
        $this->authorize('update', $department);

        $updated = $this->departmentService->update($department, $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $department->id],
        ]));

        return new DepartmentResource($updated);
    }
    
    public function destroy(Request $request, Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return response()->json(['message' => 'Department deleted']);
    }
}
