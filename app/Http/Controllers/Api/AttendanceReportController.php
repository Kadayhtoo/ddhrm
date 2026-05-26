<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
    ) {}

    public function widgets(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->attendanceService->dashboardWidgets($request->user(), $request->query('date')),
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $report = $this->attendanceService->dailyReport($request->user(), $this->filters($request));

        return response()->json([
            'data' => [
                'date' => $report['date'],
                'summary' => $report['summary'],
                'records' => AttendanceResource::collection($report['records']),
            ],
        ]);
    }

    public function monthly(Request $request): JsonResponse
    {
        $report = $this->attendanceService->monthlyReport($request->user(), $this->filters($request));

        return response()->json([
            'data' => [
                'year' => $report['year'],
                'month' => $report['month'],
                'summary' => $report['summary'],
                'records' => AttendanceResource::collection($report['records']),
            ],
        ]);
    }

    public function employee(Request $request, int $user): JsonResponse
    {
        return response()->json([
            'data' => $this->attendanceService->employeeSummary($request->user(), $user, $this->filters($request)),
        ]);
    }

    protected function filters(Request $request): array
    {
        return [
            'user_id' => $request->query('user_id'),
            'department_id' => $request->query('department_id'),
            'status' => $request->query('status'),
            'date' => $request->query('date'),
            'year' => $request->query('year'),
            'month' => $request->query('month'),
            'search' => $request->query('search'),
        ];
    }
}
