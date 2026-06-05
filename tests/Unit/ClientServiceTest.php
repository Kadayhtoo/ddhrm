<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Services\ClientService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    public function test_get_clients_list_returns_paginator(): void
    {
        $client = new Client([
            'id' => 1,
            'first_name' => 'Unit',
            'last_name' => 'Client',
            'email' => 'unit.client@example.com',
            'phone' => '5551112222',
            'address' => '123 Unit Road',
            'country' => 'USA',
            'city' => 'Denver',
            'township' => 'Central',
        ]);

        $paginator = new LengthAwarePaginator(
            new Collection([$client]),
            1,
            15,
            1
        );

        $this->mock(ClientRepositoryInterface::class, function ($mock) use ($paginator) {
            $mock->shouldReceive('getAllPaginated')
                ->once()
                ->with(15)
                ->andReturn($paginator);
        });

        $service = app(ClientService::class);

        $this->assertSame($paginator, $service->getClientsList(15));
    }

    public function test_register_client_delegates_to_repository(): void
    {
        $attributes = [
            'first_name' => 'Register',
            'last_name' => 'Client',
            'email' => 'register.client@example.com',
            'phone' => '5553334444',
            'address' => '456 Service Blvd',
            'country' => 'USA',
            'city' => 'Austin',
            'township' => 'South',
        ];

        $client = new Client($attributes);

        $this->mock(ClientRepositoryInterface::class, function ($mock) use ($attributes, $client) {
            $mock->shouldReceive('create')
                ->once()
                ->with($attributes)
                ->andReturn($client);
        });

        $service = app(ClientService::class);

        $this->assertSame($client, $service->registerClient($attributes));
    }

    public function test_update_client_returns_updated_client(): void
    {
        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Client',
            'email' => 'updated.client@example.com',
            'phone' => '5557778888',
            'address' => '789 Updated Ave',
            'country' => 'USA',
            'city' => 'Dallas',
            'township' => 'East',
        ];

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('refresh')->once()->andReturnSelf();

        $this->mock(ClientRepositoryInterface::class, function ($mock) use ($client, $data) {
            $mock->shouldReceive('findById')
                ->once()
                ->with(1)
                ->andReturn($client);
            $mock->shouldReceive('update')
                ->once()
                ->with($client, $data)
                ->andReturn(true);
        });

        $service = app(ClientService::class);

        $this->assertSame($client, $service->updateClient(1, $data));
    }

    public function test_delete_client_returns_false_when_not_found(): void
    {
        $this->mock(ClientRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('findById')
                ->once()
                ->with(999)
                ->andReturnNull();
        });

        $service = app(ClientService::class);

        $this->assertFalse($service->deleteClient(999));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
