<?php

namespace App\Policies;

use App\Models\AttendanceRequest;
use App\Models\User;

class AttendanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, AttendanceRequest $attendanceRequest): bool
    {
        return $user->hasPermission('attendance.manage') || $user->id === $attendanceRequest->user_id;
    }

    public function review(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }
}
