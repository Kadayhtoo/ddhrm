<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attendance_id' => $this->attendance_id,
            'user_id' => $this->user_id,
            'requested_by' => $this->requested_by,
            'reviewed_by' => $this->reviewed_by,
            'type' => $this->type,
            'requested_date' => $this->requested_date?->toDateString(),
            'requested_clock_in_at' => $this->requested_clock_in_at?->toIso8601String(),
            'requested_clock_out_at' => $this->requested_clock_out_at?->toIso8601String(),
            'requested_status' => $this->requested_status,
            'reason' => $this->reason,
            'status' => $this->status,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewer_note' => $this->reviewer_note,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'department' => $this->user?->department ? [
                    'id' => $this->user->department->id,
                    'name' => $this->user->department->name,
                ] : null,
            ]),
            'attendance' => new AttendanceResource($this->whenLoaded('attendance')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
