<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->query('per_page', 15);
        $invoices = $this->invoiceService->getInvoicesList((int)$perPage);
        
        return InvoiceResource::collection($invoices);
    }

    public function getNextInvoiceNumber(): JsonResponse
    {
        $nextNumber = $this->invoiceService->generateNextInvoiceNumber();
        return response()->json([
            'success' => true,
            'invoice_id' => $nextNumber
        ]);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice along with line items created successfully.',
                'data' => new InvoiceResource($invoice->load('items')) 
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $invoiceNumber): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoiceByNumber($invoiceNumber);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new InvoiceResource($invoice)
        ]);
    }

    // public function update(UpdateInvoiceRequest $request, string $invoiceNumber): JsonResponse
    // {
    //     $invoice = $this->invoiceService->getInvoiceByNumber($invoiceNumber);
    //     if (!$invoice) {
    //         return response()->json([
    //             'message' => 'Invoice not found.'
    //         ], 404);
    //     }
    //     try {
    //         $data = $request->validated();

    //         if ($request->hasFile('payment_attachment')) {
    //             if ($invoice->payment_attachment) {
    //                 Storage::disk('public')->delete($invoice->payment_attachment);
    //             }
    //             $data['payment_attachment'] = $request
    //                 ->file('payment_attachment')
    //                 ->store('payment-attachments', 'public');
    //         }

    //         $this->invoiceService->updateInvoice($invoice, $data);
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Invoice and line items updated successfully.',
    //             'data' => new InvoiceResource($invoice->fresh())
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update invoice.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function update(UpdateInvoiceRequest $request, string $invoiceNumber): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoiceByNumber($invoiceNumber);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        try {
            $data = $request->validated();

            if ($request->hasFile('payment_attachment')) {
                if ($invoice->payment_attachment) {
                    Storage::disk('public')->delete($invoice->payment_attachment);
                }
                
                $data['payment_attachment'] = $request->file('payment_attachment')
                    ->store('payment-attachments', 'public');
            } else {
                unset($data['payment_attachment']);
            }

            $this->invoiceService->updateInvoice($invoice, $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'data' => new InvoiceResource($invoice->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(string $invoiceNumber): JsonResponse
    {
        $deleted = $this->invoiceService->deleteInvoice($invoiceNumber);
        if (!$deleted) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }
        return response()->json([
            'success' => true, 
            'message' => 'Invoice and its items deleted successfully.'
        ]);
    }

    public function indexByClient($clientId): JsonResponse
    {
        $invoices = $this->invoiceService->getInvoicesForClient($clientId);
        
        return response()->json(['data' => InvoiceResource::collection($invoices)]);
    }
}