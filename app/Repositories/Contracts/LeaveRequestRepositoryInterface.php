<?php

namespace App\Repositories\Contracts;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeaveRequestRepositoryInterface
{
public function paginateByRole(User $user, int $perPage = 15, ?string $search = null, ?string $scope = 'my_requests', ?string $dateFilter = null): LengthAwarePaginator;
    public function create(array $attributes): LeaveRequest;

    public function findById(int $id): LeaveRequest;

    public function update(LeaveRequest $leaveRequest, array $attributes): LeaveRequest; 

    public function cancel(int $id): LeaveRequest;

    public function findBalance(int $userId, int $leaveRuleId): ?LeaveBalance;

    public function createBalance(array $attributes): LeaveBalance;

    public function updateBalance(LeaveBalance $balance, array $attributes): LeaveBalance; 
}