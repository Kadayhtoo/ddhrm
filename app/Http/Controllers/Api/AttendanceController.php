<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClockInRequest;
use App\Http\Requests\ClockOutRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        return AttendanceResource::collection(
            $this->attendanceService->history($request->user(), $this->filters($request), $perPage)
        );
    }

    public function today(Request $request): JsonResponse
    {
        $attendance = $this->attendanceService->today($request->user());

        return response()->json([
            'data' => $attendance ? new AttendanceResource($attendance) : null,
        ]);
    }

    public function show(Request $request, int $attendance): AttendanceResource
    {
        return new AttendanceResource(
            $this->attendanceService->findVisible($attendance, $request->user())
        );
    }

    public function checkIn(ClockInRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->checkIn($request->user(), $request->validated('notes'));

        return (new AttendanceResource($attendance))
            ->response()
            ->setStatusCode(201);
    }

    public function checkOut(ClockOutRequest $request): AttendanceResource
    {
        return new AttendanceResource(
            $this->attendanceService->checkOut($request->user(), $request->validated('notes'))
        );
    }

    protected function filters(Request $request): array
    {
        return [
            'user_id' => $request->query('user_id'),
            'department_id' => $request->query('department_id'),
            'status' => $request->query('status'),
            'date' => $request->query('date'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'search' => $request->query('search'),
        ];
    }
}
