<?php

namespace App\Services;

use App\Models\Estimate;
use App\Repositories\Contracts\EstimateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EstimateService
{
    protected const DEFAULT_TERMS = 'Please send this estimate. Payment terms to be agreed.';

    protected EstimateRepositoryInterface $estimateRepo;

    public function __construct(EstimateRepositoryInterface $estimateRepo)
    {
        $this->estimateRepo = $estimateRepo;
    }

    public function getEstimatesList(int $perPage = 15): LengthAwarePaginator
    {
        return $this->estimateRepo->getAllPaginated($perPage);
    }

    public function getEstimateByNumber(string $estimateNumber): ?Estimate
    {
        return $this->estimateRepo->findByNumber($estimateNumber);
    }

    public function generateNextEstimateNumber(): string
    {
        $latestNumber = $this->estimateRepo->getLatestEstimateNumber();
        $currentYear = now()->year;

        if (!$latestNumber) {
            return "{$currentYear}-000001";
        }

        $parts = explode('-', $latestNumber);
        $nextSequence = str_pad((int)$parts[1] + 1, 6, '0', STR_PAD_LEFT);

        return "{$currentYear}-{$nextSequence}";
    }

    public function createEstimate(array $data): Estimate
    {
        $calculations = $this->calculateBillingTotals($data['items'] ?? []);

        $estimateData = array_merge($data, [
            'sub_total'   => $calculations['sub_total'],
            'grand_total' => $calculations['grand_total'],
            'terms'       => $data['terms'] ?? self::DEFAULT_TERMS,
        ]);

        unset($estimateData['items']);

        return $this->estimateRepo->create($estimateData, $calculations['processed_items']);
    }

    public function updateEstimate(Estimate $estimate, array $data): bool
    {
        $calculations = $this->calculateBillingTotals($data['items'] ?? []);

        $estimateData = array_merge($data, [
            'sub_total'   => $calculations['sub_total'],
            'grand_total' => $calculations['grand_total'],
            'terms'       => $data['terms'] ?? self::DEFAULT_TERMS,
        ]);

        unset($estimateData['items']);

        return $this->estimateRepo->update($estimate, $estimateData, $calculations['processed_items']);
    }

    public function deleteEstimate(string $estimateNumber): bool
    {
        $estimate = $this->estimateRepo->findByNumber($estimateNumber);
        if (!$estimate) {
            return false;
        }
        return $this->estimateRepo->delete($estimate);
    }

    private function calculateBillingTotals(array $items): array
    {
        $subTotal = 0;
        $processedItems = [];

        foreach ($items as $item) {
            $lineTotal = ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
            $subTotal += $lineTotal;

            $processedItems[] = [
                'name'        => $item['name'],
                'quantity'    => (int)$item['quantity'],
                'price'       => (float)$item['price'],
                'total'       => (float)$lineTotal,
                'item_type'   => $item['item_type'] ?? null,
                'description' => $item['description'] ?? null,
            ];
        }

        return [
            'sub_total'       => (float)$subTotal,
            'grand_total'     => (float)max(0, $subTotal),
            'processed_items' => $processedItems
        ];
    }

    public function getEstimatesForClient(int $clientId)
    {
        return $this->estimateRepo->getEstimatesByClient($clientId);
    }
}
