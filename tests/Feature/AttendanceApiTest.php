<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_employee_can_check_in_as_present_within_grace_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-26 09:14:00'));
        $staff = $this->staffUser();
        Sanctum::actingAs($staff);

        $this->postJson('/api/attendance/check-in')
            ->assertCreated()
            ->assertJsonPath('data.status', 'present')
            ->assertJsonPath('data.late_minutes', 0);
    }

    public function test_employee_is_late_after_grace_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-26 09:16:00'));
        $staff = $this->staffUser();
        Sanctum::actingAs($staff);

        $this->postJson('/api/attendance/check-in')
            ->assertCreated()
            ->assertJsonPath('data.status', 'late')
            ->assertJsonPath('data.late_minutes', 1);
    }

    public function test_duplicate_check_in_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-26 09:00:00'));
        $staff = $this->staffUser();
        Sanctum::actingAs($staff);

        $this->postJson('/api/attendance/check-in')->assertCreated();
        $this->postJson('/api/attendance/check-in')->assertStatus(422);
    }

    public function test_checkout_without_check_in_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-26 18:30:00'));
        $staff = $this->staffUser();
        Sanctum::actingAs($staff);

        $this->postJson('/api/attendance/check-out')->assertStatus(422);
    }

    public function test_short_work_duration_becomes_half_day(): void
    {
        $staff = $this->staffUser();
        Sanctum::actingAs($staff);

        Carbon::setTestNow(Carbon::parse('2026-05-26 09:00:00'));
        $this->postJson('/api/attendance/check-in')->assertCreated();

        Carbon::setTestNow(Carbon::parse('2026-05-26 12:00:00'));
        $this->postJson('/api/attendance/check-out')
            ->assertOk()
            ->assertJsonPath('data.status', 'half_day');
    }

    protected function staffUser(): User
    {
        $user = User::factory()->create();
        $user->roles()->sync([Role::query()->where('slug', 'staff')->firstOrFail()->id]);

        return $user;
    }
}
