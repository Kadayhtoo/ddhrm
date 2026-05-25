<?php

namespace App\Services;

use App\Models\Position;
use App\Repositories\Contracts\PositionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PositionService
{
    protected $positionRepo;

    public function __construct(PositionRepositoryInterface $positionRepo)
    {
        $this->positionRepo = $positionRepo;
    }

    public function paginate(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->positionRepo->paginatePositions($perPage, $search);
    }
    public function getAllPositions(): Collection
    {
        return $this->positionRepo->all();
    }

    public function createPosition(array $data): Position
    {
        return $this->positionRepo->create($data);
    }

    public function getPositionById(int $id): ?Position
    {
        return $this->positionRepo->find($id);
    }

    public function updatePosition(Position $position, array $data): Position
    {
        return $this->positionRepo->update($position, $data);
    }

    public function deletePosition(Position $position): bool
    {
        return $this->positionRepo->delete($position);
    }
}