<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffController extends Controller
{
    public function __construct(
        protected StaffService $staffService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $search = $request->query('search');

        return StaffResource::collection(
            $this->staffService->paginate($perPage, is_string($search) ? $search : null)
        );
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $user = $this->staffService->create($request->validated(), $request->user());

        return (new StaffResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function showDetail($id): JsonResponse
    {
        $staff = User::with(['department', 'roles'])->findOrFail($id);
        return response()->json(['data' => $staff]);
    }
    
    public function getLeaveBalances(Request $request, $id): JsonResponse
    {
        $year = $request->query('year', date('Y'));

        $balances = LeaveBalance::with(['leaveRule'])
            ->where('user_id', $id)
            ->whereHas('leaveRule.leaveRequests', function($query) use ($id, $year) {
                $query->where('user_id', $id)
                    ->where('year', $year); 
        })
            ->get();
            
        return response()->json(['data' => $balances]);
    }

    public function getLeaveRequests(Request $request, $id): JsonResponse
    {
        $year = $request->query('year', date('Y'));

        $requests = LeaveRequest::with('leaveRule')
            ->where('user_id', $id)
            ->whereYear('start_date', $year) 
            ->orderByDesc('id')
            ->get();
            
        return response()->json(['data' => $requests]);
    }
    
    public function update(UpdateStaffRequest $request, User $user): StaffResource
    {
        $updated = $this->staffService->update($user, $request->validated(), $request->user());

        return new StaffResource($updated);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->staffService->delete($user, $request->user());

        return response()->json(['message' => 'User deleted']);
    }

    public function dropdownList(): JsonResponse
    {
        $staff = User::query()
            ->select(['id', 'name', 'department_id'])
            ->with(['department:id,name'])
            ->orderBy('name')
            ->get();

        return response()->json($staff);
    }
}