<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class AttendanceSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                'office_start' => Setting::getValue('attendance.office_start', config('attendance.office_start')),
                'office_end' => Setting::getValue('attendance.office_end', config('attendance.office_end')),
                'grace_minutes' => (int) Setting::getValue('attendance.grace_minutes', config('attendance.grace_minutes')),
                'minimum_work_minutes' => (int) Setting::getValue('attendance.minimum_work_minutes', config('attendance.minimum_work_minutes')),
            ],
        ]);
    }

    public function update(AttendanceSettingsRequest $request): JsonResponse
    {
        Setting::setValue('attendance.office_start', $request->validated('office_start'));
        Setting::setValue('attendance.office_end', $request->validated('office_end'));
        Setting::setValue('attendance.grace_minutes', $request->validated('grace_minutes'));
        Setting::setValue('attendance.minimum_work_minutes', $request->validated('minimum_work_minutes'));

        return response()->json([
            'data' => [
                'message' => 'Attendance settings updated successfully.',
            ],
        ]);
    }
}
