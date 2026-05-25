<?php

namespace App\Repositories;

use App\Models\LeaveBalance;

class LeaveBalanceRepository implements LeaveBalanceRepositoryInterface
{
    public function getAllBalancesWithFilter(?string $search = null, int $perPage = 10)
    {
        $query = LeaveBalance::with(['user.department', 'leaveRule']);

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function getBalanceByUserId(int $userId)
    {
        return LeaveBalance::with(['user.department', 'leaveRule'])
            ->where('user_id', $userId)
            ->get();
    }

    public function firstOrCreateBalance(array $attributes, array $values)
    {
        return LeaveBalance::firstOrCreate($attributes, $values);
    }
}