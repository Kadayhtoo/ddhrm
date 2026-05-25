<?php

namespace App\Repositories\Contracts;
use App\Models\Position;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PositionRepositoryInterface
{
    public function all(): Collection;
    public function paginatePositions(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    public function create(array $data): Position;
    public function find(int $id): ?Position;
    public function update(Position $position, array $data): Position;
    public function delete(Position $position): bool;
}