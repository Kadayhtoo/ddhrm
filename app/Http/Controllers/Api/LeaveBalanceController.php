<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaveBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveBalanceController extends Controller
{
    protected $leaveBalanceService;

    public function __construct(LeaveBalanceService $leaveBalanceService)
    {
        $this->leaveBalanceService = $leaveBalanceService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $search = $request->input('search');

        try {
            $result = $this->leaveBalanceService->getLeaveBalancesForUser($user, $search);

            return response()->json([
                'success' => true,
                'is_admin_view' => $result['is_admin_view'],
                'data' => $result['data'],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching leave balances.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
