<?php 
namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository implements ClientRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Client::latest()->paginate($perPage);
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function findById(int $id): ?Client
    {
       return Client::with('contacts')->find($id);
    }

    public function update(Client $client, array $data): bool
    {
        return $client->update($data);
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
    }
}