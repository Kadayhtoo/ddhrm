<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function __construct(
        protected LeaveRequest $model,
        protected LeaveBalance $balanceModel
    ) {}

    public function paginateByRole(User $user, int $perPage = 15, ?string $search = null, ?string $scope = 'my_requests', ?string $dateFilter = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['user.department', 'leaveRule'])->orderByDesc('id');

        if ($user->hasRoleSlug('admin') || $user->hasRoleSlug('hr')) {
            if ($scope === 'approvals') {
                $query->where('user_id', '!=', $user->id);
            } else {
                $query->where('user_id', $user->id);
            }
        } else {
            if ($scope === 'approvals') {
                $query->where('approver_id', $user->id)
                      ->where('user_id', '!=', $user->id);
            } else {
                $query->where('user_id', $user->id);
            }
        }
        if ($dateFilter) {
            $query->whereDate('start_date', $dateFilter);
        }
        if ($search) {
            $query->where(function ($mainQuery) use ($search) {
                $mainQuery->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('leaveRule', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): LeaveRequest
    {
        return $this->model->newQuery()->with(['user', 'leaveRule'])->findOrFail($id);
    }

    public function create(array $attributes): LeaveRequest
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(LeaveRequest $leaveRequest, array $attributes): LeaveRequest
    {
        $leaveRequest->update($attributes);
        return $leaveRequest;
    }

    public function cancel(int $id): LeaveRequest
    {
        $request = $this->findById($id);
        $request->update(['status' => 'cancelled']);
        return $request;
    }

    public function findBalance(int $userId, int $leaveRuleId): ?LeaveBalance
    {
        return $this->balanceModel->newQuery()
            ->where('user_id', $userId)
            ->where('leave_rule_id', $leaveRuleId)
            ->first();
    }

    public function createBalance(array $attributes): LeaveBalance
    {
        return $this->balanceModel->newQuery()->create($attributes);
    }

    public function updateBalance(LeaveBalance $balance, array $attributes): LeaveBalance
    {
        $balance->update($attributes);
        return $balance;
    }
}