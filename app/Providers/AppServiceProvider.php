<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Role;
use App\Policies\AttendancePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\RolePolicy;
use App\Repositories\Contracts\AboutUsRepositoryInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Contracts\ContactPersonRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EstimateRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\LeaveRuleRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\StaffDocumentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\AboutUsRepository;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Eloquent\ClientRepository;
use App\Repositories\Eloquent\ContactPersonRepository;
use App\Repositories\Eloquent\DepartmentRepository;
use App\Repositories\Eloquent\EstimateRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\LeaveRequestRepository;
use App\Repositories\Eloquent\LeaveRuleRepository;
use App\Repositories\Eloquent\PositionRepository;
use App\Repositories\Eloquent\StaffDocumentRepository;
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
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(LeaveRuleRepositoryInterface::class, LeaveRuleRepository::class);
        $this->app->bind(LeaveRequestRepositoryInterface::class, LeaveRequestRepository::class);
        $this->app->bind(LeaveBalanceRepositoryInterface::class, LeaveBalanceRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class); 
        $this->app->bind(ContactPersonRepositoryInterface::class, ContactPersonRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(EstimateRepositoryInterface::class, EstimateRepository::class);
        $this->app->bind(AboutUsRepositoryInterface::class, AboutUsRepository::class);
        $this->app->bind(StaffDocumentRepositoryInterface::class, StaffDocumentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
    }
}
