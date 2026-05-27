<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('staff.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('staff.view') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('staff.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('staff.update');
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermission('staff.delete');
    }
}
