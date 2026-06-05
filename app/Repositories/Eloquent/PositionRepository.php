<?php

namespace App\Repositories\Eloquent;

use App\Models\Position;
use App\Repositories\Contracts\PositionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PositionRepository implements PositionRepositoryInterface
{
    public function all(): Collection
    {
        return Position::with('department')->get();
    }

    public function paginatePositions(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Position::with('department')
            ->orderByDesc('id');

        if ($search) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('department', function ($depQuery) use ($term) {
                        $depQuery->where('name', 'like', $term);
                    });
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Position
    {
        return Position::create($data);
    }

    public function find(int $id): ?Position
    {
        return Position::with('department')->find($id);
    }

    public function update(Position $position, array $data): Position
    {
        $position->update($data);

        return $position;
    }

    public function delete(Position $position): bool
    {
        return $position->delete();
    }
}
