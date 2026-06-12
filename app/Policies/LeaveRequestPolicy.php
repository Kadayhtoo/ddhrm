<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('leave-requests.view');
    }

    public function scopeForUser($query, $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('hr')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasPermission('leave-requests.view') || $user->id === $leaveRequest->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('leave-requests.create');
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasPermission('leave-requests.update') || $user->id === $leaveRequest->user_id;
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasPermission('leave-requests.delete') || $user->id === $leaveRequest->user_id;
    }
}
