<?php

namespace App\Services;

use App\Models\LeaveRule;
use App\Models\User;
use App\Repositories\LeaveBalanceRepositoryInterface;

class LeaveBalanceService
{
    protected $leaveBalanceRepo;

    public function __construct(LeaveBalanceRepositoryInterface $leaveBalanceRepo)
    {
        $this->leaveBalanceRepo = $leaveBalanceRepo;
    }

    public function getLeaveBalancesForUser($user, ?string $search = null)
    {
        if ($user->hasPermissionTo('leave-balances.view') || $user->hasRole('admin') || $user->hasRole('hr')) {
            $balances = $this->leaveBalanceRepo->getAllBalancesWithFilter($search);

            return ['is_admin_view' => true, 'data' => $balances];
        }

        $myBalance = $this->leaveBalanceRepo->getBalanceByUserId($user->id);

        return ['is_admin_view' => false, 'data' => $myBalance];
    }

    public function initializeBalancesForUser(User $user): void
    {
        $paidRules = LeaveRule::where('type', 'paid')->get();

        foreach ($paidRules as $rule) {
            $this->leaveBalanceRepo->firstOrCreateBalance(
                [
                    'user_id' => $user->id,
                    'leave_rule_id' => $rule->id,
                ],
                [
                    'total_allowed_days' => $rule->days ?? 0,
                    'used_days' => 0,
                    'remaining_days' => $rule->days ?? 0,
                ]
            );
        }
    }
}
