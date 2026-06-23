<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstimateRequest;
use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Resources\EstimateResource;
use App\Models\Estimate;
use App\Services\EstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;

class EstimateController extends Controller
{
    protected EstimateService $estimateService;

    public function __construct(EstimateService $estimateService)
    {
        $this->estimateService = $estimateService;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->query('per_page', 15);
        $estimates = $this->estimateService->getEstimatesList((int)$perPage);
        
        return EstimateResource::collection($estimates);
    }

    public function getNextEstimateNumber(): JsonResponse
    {
        $nextNumber = $this->estimateService->generateNextEstimateNumber();
        return response()->json([
            'success' => true,
            'estimate_id' => $nextNumber
        ]);
    }

    public function store(StoreEstimateRequest $request): JsonResponse
    {
        try {
            $estimate = $this->estimateService->createEstimate($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Estimate along with line items created successfully.',
                'data' => new EstimateResource($estimate->load('items')) 
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create estimate.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $estimateNumber): JsonResponse
    {
        $estimate = $this->estimateService->getEstimateByNumber($estimateNumber);
        if (!$estimate) {
            return response()->json(['message' => 'Estimate not found.'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new EstimateResource($estimate)
        ]);
    }

    public function update(UpdateEstimateRequest $request, string $estimateNumber): JsonResponse
    {
        $estimate = $this->estimateService->getEstimateByNumber($estimateNumber);
        if (!$estimate) {
            return response()->json(['message' => 'Estimate not found.'], 404);
        }

        try {
            $this->estimateService->updateEstimate($estimate, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Estimate and line items updated successfully.',
                'data' => new EstimateResource($estimate->refresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update estimate.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $estimateNumber): JsonResponse
    {
        $deleted = $this->estimateService->deleteEstimate($estimateNumber);
        if (!$deleted) {
            return response()->json(['message' => 'Estimate not found.'], 404);
        }
        return response()->json([
            'success' => true, 
            'message' => 'Estimate and its items deleted successfully.'
        ]);
    }

    public function indexByClient($clientId): JsonResponse
    {
        $estimates = $this->estimateService->getEstimatesForClient($clientId);
        
        return response()->json(['data' => EstimateResource::collection($estimates)]);
    }

    public function sendEstimateEmail($id)
    {
        $estimate = Estimate::with(['client', 'items'])->findOrFail($id); 
        
        if (!$estimate->client) {
            return response()->json(['message' => 'No client assigned!'], 400);
        }

        if (!filter_var($estimate->client->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Invalid email format: ' . $estimate->client->email 
            ], 400);
        }

        $company = \App\Models\AboutUs::first();

        Mail::to($estimate->client->email)->send(new \App\Mail\EstimateMail($estimate, $company));

        return response()->json(['message' => 'Estimate email sent successfully!']);
}
}
