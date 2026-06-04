<?php

namespace App\Repositories\Eloquent;

use App\Models\Estimate;
use App\Repositories\Contracts\EstimateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EstimateRepository implements EstimateRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Estimate::with('client','items')->latest()->paginate($perPage);
    }

    public function findByNumber(string $estimateNumber): ?Estimate
    {
        return Estimate::with('items', 'client')->find($estimateNumber);
    }

    public function getLatestEstimateNumber(): ?string
    {
        return Estimate::whereYear('created_at', now()->year)
                      ->latest()
                      ->value('estimate_id');
    }

    public function create(array $headerData, array $itemsData): Estimate
    {
        return DB::transaction(function () use ($headerData, $itemsData) {
            $estimate = Estimate::create($headerData);

            foreach ($itemsData as $item) {
                $estimate->items()->create($item);
            }

            return $estimate;
        });
    }

    public function update(Estimate $estimate, array $headerData, array $itemsData): bool
    {
        return DB::transaction(function () use ($estimate, $headerData, $itemsData) {
            $estimate->update($headerData);

            $estimate->items()->delete();

            foreach ($itemsData as $item) {
                $estimate->items()->create($item);
            }

            return true;
        });
    }

    public function delete(Estimate $estimate): bool
    {
        return DB::transaction(function () use ($estimate) {
            $estimate->items()->delete();
            return $estimate->delete();
        });
    }

    public function getEstimatesByClient(int $clientId)
    {
        return Estimate::with(['client', 'items'])
                    ->where('client_id', $clientId)
                    ->latest()
                    ->get();
    }
}
