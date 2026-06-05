<?php 
namespace App\Repositories\Contracts;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClientRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Client;

    public function findById(int $id): ?Client;

    public function update(Client $client, array $data): bool;

    public function delete(Client $client): bool;
}