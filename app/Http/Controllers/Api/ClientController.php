<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $clients = $this->clientService->getClientsList((int)$perPage);
        
        return response()->json($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->registerClient($request->validated());
        
        return response()->json([
            'message' => 'Client created successfully.',
            'data' => $client
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $client = $this->clientService->getClient($id);
        if (!$client) {
            return response()->json(['message' => 'Client not found.'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new ClientResource($client)
        ], 200);
    }

    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = $this->clientService->updateClient($id, $request->validated());
        if (!$client) {
            return response()->json(['message' => 'Client not found.'], 404);
        }
        
        return response()->json([
            'message' => 'Client updated successfully.',
            'data' => $client
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->clientService->deleteClient($id);
        if (!$deleted) {
            return response()->json(['message' => 'Client not found.'], 404);
        }
        
        return response()->json(['message' => 'Client deleted successfully.']);
    }
    
    public function getLocationConfig(): JsonResponse
    {
        $locationFile = config_path('location.php');
        
        if (file_exists($locationFile)) {
            $config = include $locationFile;
            $locations = $config['countries'] ?? [];
        } else {
            $locations = [];
        }

        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }
}