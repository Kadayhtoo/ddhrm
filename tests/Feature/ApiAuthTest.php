<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'auth-test@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $role = Role::query()->where('slug', 'staff')->firstOrFail();
        $user->roles()->sync([$role->id]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'auth-test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'email', 'name', 'roles', 'permissions'],
            ]);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }
}
