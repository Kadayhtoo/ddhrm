<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $search = $request->query('search');

        $scope = $request->query('scope', 'my_requests');
        $dateFilter = $request->query('date_filter');

        $currentUser = Auth::user();

        $requests = $this->service->getPaginatedRequests(
            $currentUser,
            $perPage,
            $search,
            $scope,
            $dateFilter
        );

        return response()->json($requests);
    }

    public function show($id)
    {
        try {
            $leaveRequest = $this->service->getLeaveDetails($id);
            return response()->json($leaveRequest);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Record not found'], 404);
        }
    }
    
    // public function store(StoreLeaveRequest $request): JsonResponse
    // {
    //     try {
    //         $validated = $request->validated();
    //         $currentUser = Auth::user();

    //         $validated['requested_by'] = $currentUser->id;
    //         if (empty($validated['user_id'])) {
    //             $validated['user_id'] = $currentUser->id;
    //         }
    //         $leaveRequest = $this->service->applyLeave($validated, $request->file('attachment'), $currentUser);

    //         return response()->json([
    //             'message' => 'Leave request submitted successfully.',
    //             'data' => $leaveRequest,
    //         ], 201);

    //     } catch (\Exception $e) {
    //         return response()->json(['message' => $e->getMessage()], 422);
    //     }

    // }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $currentUser = Auth::user();

            $targetUserId = !empty($validated['user_id']) ? $validated['user_id'] : $currentUser->id;
            
            $validated['user_id'] = $targetUserId;
            $validated['requested_by'] = $currentUser->id;

            // leave rule to check to check allowed days
            $leaveRule = \App\Models\LeaveRule::find($validated['leave_rule_id']);

            $balance = \App\Models\LeaveBalance::where('user_id', $targetUserId)
                ->where('leave_rule_id', $validated['leave_rule_id'])
                ->first();

            $remainingDays = $balance ? (float) $balance->remaining_days : (float) $leaveRule->days;

            if ((float) $validated['total_days'] > $remainingDays) {
                return response()->json([
                    'message' => "Insufficient leave balance. The user only has {$remainingDays} days remaining."
                ], 422);
            }

            $leaveRequest = $this->service->applyLeave($validated, $request->file('attachment'), $currentUser);

            return response()->json([
                'message' => 'Leave request submitted successfully.',
                'data' => $leaveRequest,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function changeStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        try {
            $currentUser = Auth::user();
            $updatedRequest = $this->service->handleApproval($id, $validated['status'], $currentUser);

            return response()->json([
                'message' => 'Leave request status updated successfully.',
                'data' => $updatedRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            $cancelledRequest = $this->service->cancelLeaveRequest($id, $currentUser);

            return response()->json([
                'message' => 'Leave request has been cancelled successfully.',
                'data' => $cancelledRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(UpdateLeaveRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $currentUser = Auth::user();

            $updatedRequest = $this->service->updateLeaveRequest(
                (int) $id,
                $validated,
                $request->file('attachment'),
                $currentUser
            );

            return response()->json([
                'message' => 'Leave request updated successfully.',
                'data' => $updatedRequest,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
