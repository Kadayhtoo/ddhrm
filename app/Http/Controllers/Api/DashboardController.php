<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $users,
    ) {}

    // public function summary(Request $request): JsonResponse
    // {
    //     $user = $request->user();
    //     $user?->loadMissing('roles');

    //     if (! $user || ! $user->hasPermission('dashboard.view')) {
    //         abort(403);
    //     }

    //     $activeStaff = $this->users->allActive()->count();

    //     return response()->json([
    //         'welcome' => 'DDHRM',
    //         'role_slugs' => $user->roles->pluck('slug')->values()->all(),
    //         'modules' => [
    //             'attendance' => ['label' => 'Attendance', 'value' => 'Clock in / out'],
    //             'staff' => ['label' => 'Active staff', 'value' => $activeStaff],
    //             'payroll' => ['label' => 'Payroll', 'value' => 'Generate & payslips'],
    //             'leave' => ['label' => 'Leave', 'value' => 'Rules & approvals'],
    //             'invoices' => ['label' => 'Client invoices', 'value' => 'Projects & PDF'],
    //         ],
    //     ]);
    // }
 
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->loadMissing('roles');

        if (! $user || ! $user->hasPermission('dashboard.view')) abort(403);

        $isAdminOrHr = $user->roles->whereIn('slug', ['admin', 'hr'])->isNotEmpty();

        return response()->json([
            'welcome' => 'DDHRM',
            'role_slugs' => $user->roles->pluck('slug')->values()->all(),
            'modules' => [
                'attendance' => ['label' => 'Attendance', 'value' => 'Clock in / out'],
                'staff' => [
                    'label' => $isAdminOrHr ? 'Active staff' : 'Leave Requests',
                    'value' => $isAdminOrHr 
                        ? $this->users->allActive()->count() 
                        : $user->leaveRequests()
                            ->join('leave_rules', 'leave_requests.leave_rule_id', '=', 'leave_rules.id')
                            ->selectRaw('leave_rules.name as type_name, count(leave_requests.id) as total')
                            ->groupBy('leave_rules.name')
                            ->pluck('total', 'type_name') 
                ],
                'payroll' => [
                    'label' => 'Payroll',
                    'value' => $isAdminOrHr ? 'Payrolls & Payslips' : 'View My Payroll',
                    'link' => $isAdminOrHr ? 'payroll' : 'payroll/history'
                ],
               
            ],
        ]);
    }

}
