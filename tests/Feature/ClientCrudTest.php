<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function actingAsUser(): User
    {
        return User::factory()->create([
            'email' => 'client-test-user@example.com',
        ]);
    }

    public function test_guest_cannot_access_client_endpoints(): void
    {
        $this->getJson('/api/client')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_clients(): void
    {
        Client::create([
            'first_name' => 'Acme',
            'last_name' => 'Corp',
            'email' => 'acme@example.com',
            'phone' => '1234567890',
            'address' => '123 Main Street',
            'country' => 'USA',
            'city' => 'New York',
            'township' => 'Brooklyn',
        ]);

        Sanctum::actingAs($this->actingAsUser());

        $this->getJson('/api/client')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_authenticated_user_can_create_client(): void
    {
        Sanctum::actingAs($this->actingAsUser());

        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '5551234567',
            'address' => '456 Elm Street',
            'country' => 'USA',
            'city' => 'Boston',
            'township' => 'Downtown',
        ];

        $this->postJson('/api/client', $payload)
            ->assertCreated()
            ->assertJsonFragment(['message' => 'Client created successfully.'])
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.email', 'john.doe@example.com');
    }

    public function test_authenticated_user_can_view_client(): void
    {
        $client = Client::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '5559876543',
            'address' => '789 Oak Avenue',
            'country' => 'USA',
            'city' => 'Chicago',
            'township' => 'Lincoln Park',
        ]);

        Sanctum::actingAs($this->actingAsUser());

        $this->getJson("/api/client/{$client->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $client->id)
            ->assertJsonPath('data.email', 'jane.smith@example.com');
    }

    public function test_authenticated_user_can_update_client(): void
    {
        $client = Client::create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'old@example.com',
            'phone' => '5550000000',
            'address' => '1 Old Road',
            'country' => 'USA',
            'city' => 'Austin',
            'township' => 'North',
        ]);

        Sanctum::actingAs($this->actingAsUser());

        $payload = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '5559999999',
            'address' => '2 New Road',
            'country' => 'USA',
            'city' => 'Austin',
            'township' => 'North',
        ];

        $this->putJson("/api/client/{$client->id}", $payload)
            ->assertOk()
            ->assertJsonFragment(['message' => 'Client updated successfully.'])
            ->assertJsonPath('data.email', 'updated@example.com');
    }

    public function test_authenticated_user_can_delete_client(): void
    {
        $client = Client::create([
            'first_name' => 'Delete',
            'last_name' => 'Me',
            'email' => 'delete.me@example.com',
            'phone' => '5554443333',
            'address' => '3 Gone Street',
            'country' => 'USA',
            'city' => 'Seattle',
            'township' => 'Capitol Hill',
        ]);

        Sanctum::actingAs($this->actingAsUser());

        $this->deleteJson("/api/client/{$client->id}")
            ->assertOk()
            ->assertJson(['message' => 'Client deleted successfully.']);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
