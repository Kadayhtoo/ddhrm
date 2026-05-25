<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRuleRequest;
use App\Http\Requests\UpdateLeaveRuleRequest;
use App\Http\Resources\LeaveRuleResource;
use App\Services\LeaveRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaveRuleController extends Controller
{
    public function __construct(
        protected LeaveRuleService $leaveRuleService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $search = $request->query('search');

        return LeaveRuleResource::collection(
            $this->leaveRuleService->paginate($perPage, is_string($search) ? $search : null)
        );
    }

    public function store(StoreLeaveRuleRequest $request): JsonResponse
    {
        $leaveRule = $this->leaveRuleService->create($request->validated());

        return (new LeaveRuleResource($leaveRule))
            ->response()
            ->setStatusCode(201);
    }

    public function show($id): LeaveRuleResource
    {
        $leaveRule = $this->leaveRuleService->findById($id);
        
        if (!$leaveRule) {
            abort(404, 'Leave rule not found');
        }

        return new LeaveRuleResource($leaveRule);
    }

    public function update(UpdateLeaveRuleRequest $request, $id): LeaveRuleResource
    {
        $leaveRule = $this->leaveRuleService->findById($id);

        if (!$leaveRule) {
            abort(404, 'Leave rule not found');
        }

        $updated = $this->leaveRuleService->update($leaveRule, $request->validated());

        return new LeaveRuleResource($updated);
    }

    public function destroy($id): JsonResponse
    {
        $leaveRule = $this->leaveRuleService->findById($id);

        if (!$leaveRule) {
            abort(404, 'Leave rule not found');
        }

        $this->leaveRuleService->delete($leaveRule);

        return response()->json(['message' => 'Leave rule deleted']);
    }
}