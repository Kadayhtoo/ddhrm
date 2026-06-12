<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;
    
    public function findByNumber(string $invoiceNumber): ?Invoice;
    
    public function getLatestInvoiceNumber(): ?string;
    
    public function create(array $headerData, array $itemsData): Invoice;
    
    public function update(Invoice $invoice, array $headerData, array $itemsData): bool;
    
    public function delete(Invoice $invoice): bool;

    public function getInvoicesByClient(int $clientId);
}