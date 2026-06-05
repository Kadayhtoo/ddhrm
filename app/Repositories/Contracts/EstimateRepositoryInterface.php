<?php

namespace App\Repositories\Contracts;

use App\Models\Estimate;
use Illuminate\Pagination\LengthAwarePaginator;

interface EstimateRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function findByNumber(string $estimateNumber): ?Estimate;

    public function getLatestEstimateNumber(): ?string;

    public function create(array $headerData, array $itemsData): Estimate;

    public function update(Estimate $estimate, array $headerData, array $itemsData): bool;

    public function delete(Estimate $estimate): bool;

    public function getEstimatesByClient(int $clientId);
}
