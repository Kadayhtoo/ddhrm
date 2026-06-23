<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PositionController extends Controller
{
    protected $positionService;

    public function __construct(PositionService $positionService)
    {
        $this->positionService = $positionService;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $search = $request->query('search');

        $positions = $this->positionService->paginate(
            $perPage,
            is_string($search) ? $search : null
        );

        return PositionResource::collection($positions);
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        $position = $this->positionService->createPosition($request->validated());

        return (new PositionResource($position))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $position = $this->positionService->getPositionById($id);

        if (! $position) {
            return response()->json(['success' => false, 'message' => 'Position not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $position], 200);
    }

    public function update(UpdatePositionRequest $request, Position $position): PositionResource
    {
        $updatedPosition = $this->positionService->updatePosition($position, $request->validated());
        return new PositionResource($updatedPosition);
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->positionService->deletePosition($position);

        return response()->json(['success' => true, 'message' => 'Position deleted successfully'], 200);
    }
}
