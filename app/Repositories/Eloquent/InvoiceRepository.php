<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::with('client','items')->latest()->paginate($perPage);
    }

    public function findByNumber(string $invoiceNumber): ?Invoice
    {
        return Invoice::with('items', 'client')->find($invoiceNumber);
    }

    public function getLatestInvoiceNumber(): ?string
    {
        return Invoice::whereYear('created_at', now()->year)
                      ->latest()
                      ->value('invoice_id');
    }

    public function create(array $headerData, array $itemsData): Invoice
    {
        return DB::transaction(function () use ($headerData, $itemsData) {
            $invoice = Invoice::create($headerData);

            foreach ($itemsData as $item) {
                $invoice->items()->create($item);
            }

            return $invoice;
        });
    }

    public function update(Invoice $invoice, array $headerData, array $itemsData): bool
    {
        return DB::transaction(function () use ($invoice, $headerData, $itemsData) {
            $invoice->update($headerData);

            $invoice->items()->delete();

            foreach ($itemsData as $item) {
                $invoice->items()->create($item);
            }

            return true;
        });
    }

    public function delete(Invoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            return $invoice->delete();
        });
    }

    public function getInvoicesByClient(int $clientId)
    {
        return Invoice::with(['client', 'items'])
                    ->where('client_id', $clientId)
                    ->latest()
                    ->get();
    }
}