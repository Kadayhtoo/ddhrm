<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_list_staff(): void
    {
        $admin = User::factory()->create(['email' => 'admin-list@example.com']);
        $admin->roles()->sync([Role::query()->where('slug', 'admin')->first()->id]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/staff')->assertOk()->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_staff_cannot_list_staff_directory(): void
    {
        $staff = User::factory()->create(['email' => 'plain-staff@example.com']);
        $staff->roles()->sync([Role::query()->where('slug', 'staff')->first()->id]);

        Sanctum::actingAs($staff);

        $this->getJson('/api/staff')->assertForbidden();
    }
}
