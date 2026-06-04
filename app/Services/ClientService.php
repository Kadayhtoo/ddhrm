<?php 

namespace App\Services;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientService
{
    protected ClientRepositoryInterface $clientRepo;

    public function __construct(ClientRepositoryInterface $clientRepo)
    {
        $this->clientRepo = $clientRepo;
    }

    public function getClientsList(int $perPage = 15): LengthAwarePaginator
    {
        return $this->clientRepo->getAllPaginated($perPage);
    }

    public function registerClient(array $data): Client
    {
        return $this->clientRepo->create($data);
    }

    public function getClient(int $id): ?Client
    {
        return $this->clientRepo->findById($id);
    }

    public function updateClient(int $id, array $data): ?Client
    {
        $client = $this->clientRepo->findById($id);
        if (!$client) {
            return null;
        }

        $this->clientRepo->update($client, $data);
        return $client->refresh();
    }

    public function deleteClient(int $id): bool
    {
        $client = $this->clientRepo->findById($id);
        if (!$client) {
            return false;
        }

        return $this->clientRepo->delete($client);
    }
}