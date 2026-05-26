<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->hasPermission('attendance.manage') || $user->id === $attendance->user_id;
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }
}
