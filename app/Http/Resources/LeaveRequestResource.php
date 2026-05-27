<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'department_id' => $this->department_id,
            'approver_id' => $this->approver_id,
            'leave_rule_id' => $this->leave_rule_id,
            'leave_session' => $this->leave_session,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'is_approve' => $this->is_approve,
            'is_approve_hr' => $this->is_approve_hr,

            'attachment' => $this->attachment,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'department_id' => $this->user?->department_id,
            ],

            'leave_rule' => [
                'id' => $this->leaveRule?->id,
                'name' => $this->leaveRule?->name,
            ],

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
