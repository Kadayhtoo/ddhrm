<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Attendance;
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

    public function getLeaveBalances($id): JsonResponse
    {
        $balances = LeaveBalance::with('leaveRule')
            ->where('user_id', $id)
            ->get();

        return response()->json(['data' => $balances]);
    }

    public function getLeaveRequests($id): JsonResponse
    {
        $requests = LeaveRequest::with('leaveRule')
            ->where('user_id', $id)
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $requests]);
    }

    public function getAttendances($id): JsonResponse
    {
        $attendances = Attendance::query()
            ->where('user_id', $id)
            ->orderByDesc('attendance_date')
            ->limit(60)
            ->get()
            ->map(fn (Attendance $attendance) => [
                'id' => $attendance->id,
                'date' => $attendance->attendance_date?->toDateString(),
                'check_in' => $attendance->clock_in_at?->format('H:i'),
                'check_out' => $attendance->clock_out_at?->format('H:i'),
                'work_hours' => round(((int) $attendance->work_minutes) / 60, 2),
                'status' => $attendance->status,
            ]);

        return response()->json(['data' => $attendances]);
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
