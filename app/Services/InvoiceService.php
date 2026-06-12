<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    protected const DEFAULT_TERMS = 'Please send payment within 7 days of receiving this invoice.';

    protected InvoiceRepositoryInterface $invoiceRepo;

    public function __construct(InvoiceRepositoryInterface $invoiceRepo)
    {
        $this->invoiceRepo = $invoiceRepo;
    }

    public function getInvoicesList(int $perPage = 15): LengthAwarePaginator
    {
        return $this->invoiceRepo->getAllPaginated($perPage);
    }

    public function getInvoiceByNumber(string $invoiceNumber): ?Invoice
    {
        return $this->invoiceRepo->findByNumber($invoiceNumber);
    }

    public function generateNextInvoiceNumber(): string
    {
        $latestNumber = $this->invoiceRepo->getLatestInvoiceNumber();
        $currentYear = now()->year;

        if (!$latestNumber) {
            return "{$currentYear}-000001";
        }

        $parts = explode('-', $latestNumber);
        $nextSequence = str_pad((int)$parts[1] + 1, 6, '0', STR_PAD_LEFT);

        return "{$currentYear}-{$nextSequence}";
    }

    public function createInvoice(array $data): Invoice
    {
        $calculations = $this->calculateBillingTotals(
            $data['items'] ?? [], 
            $data['discount_type'], 
            $data['discount_value'] ?? 0
        );

        $invoiceData = array_merge($data, [
            'sub_total'   => $calculations['sub_total'],
            'grand_total' => $calculations['grand_total'],
            'terms'       => $data['terms'] ?? self::DEFAULT_TERMS,
        ]);

        unset($invoiceData['items']);

        return $this->invoiceRepo->create($invoiceData, $calculations['processed_items']);
    }

    public function updateInvoice(Invoice $invoice, array $data): bool
    {
        $calculations = $this->calculateBillingTotals(
            $data['items'] ?? [], 
            $data['discount_type'], 
            $data['discount_value'] ?? 0
        );

        $invoiceData = array_merge($data, [
            'sub_total'   => $calculations['sub_total'],
            'grand_total' => $calculations['grand_total'],
            'terms'       => $data['terms'] ?? self::DEFAULT_TERMS,
        ]);

        unset($invoiceData['items']);

        return $this->invoiceRepo->update($invoice, $invoiceData, $calculations['processed_items']);
    }

    public function deleteInvoice(string $invoiceNumber): bool
    {
        $invoice = $this->invoiceRepo->findByNumber($invoiceNumber);
        if (!$invoice) {
            return false;
        }
        return $this->invoiceRepo->delete($invoice);
    }

    private function calculateBillingTotals(array $items, string $discountType, float $discountValue, string $type = 'invoice'): array
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

        $discountAmount = 0;

        if ($type === 'invoice') {
            if ($discountType === 'percentage') {
                $discountAmount = $subTotal * ($discountValue / 100);
            } else {
                $discountAmount = $discountValue;
            }
        }

        return [
            'sub_total'       => (float)$subTotal,
            'grand_total'     => (float)max(0, $subTotal - $discountAmount), 
            'processed_items' => $processedItems
        ];
    }
    
    public function getInvoicesForClient(int $clientId)
    {
        return $this->invoiceRepo->getInvoicesByClient($clientId);
    }
}