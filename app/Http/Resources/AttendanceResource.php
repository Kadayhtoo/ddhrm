<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'clock_in_at' => $this->clock_in_at?->toIso8601String(),
            'clock_out_at' => $this->clock_out_at?->toIso8601String(),
            'check_in' => $this->clock_in_at?->format('H:i'),
            'check_out' => $this->clock_out_at?->format('H:i'),
            'work_minutes' => $this->work_minutes,
            'work_hours' => round(((int) $this->work_minutes) / 60, 2),
            'late_minutes' => $this->late_minutes,
            'status' => $this->status,
            'source' => $this->source,
            'is_auto_closed' => $this->is_auto_closed,
            'notes' => $this->notes,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'department_id' => $this->user?->department_id,
                'department' => $this->user?->department ? [
                    'id' => $this->user->department->id,
                    'name' => $this->user->department->name,
                ] : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
