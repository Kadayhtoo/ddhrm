<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\Role;
use App\Policies\AttendancePolicy;
use App\Policies\AttendanceRequestPolicy;
use App\Policies\RolePolicy;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\AttendanceRequestRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\LeaveRuleRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Eloquent\AttendanceRequestRepository;
use App\Repositories\Eloquent\DepartmentRepository;
use App\Repositories\Eloquent\LeaveRequestRepository;
use App\Repositories\Eloquent\LeaveRuleRepository;
use App\Repositories\Eloquent\PositionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\LeaveBalanceRepository;
use App\Repositories\LeaveBalanceRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(AttendanceRequestRepositoryInterface::class, AttendanceRequestRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(LeaveRuleRepositoryInterface::class, LeaveRuleRepository::class);
        $this->app->bind(LeaveRequestRepositoryInterface::class, LeaveRequestRepository::class);
        $this->app->bind(LeaveBalanceRepositoryInterface::class, LeaveBalanceRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(AttendanceRequest::class, AttendanceRequestPolicy::class);
    }
}
