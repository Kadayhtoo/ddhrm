<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'requested_by',
        'department_id',
        'approver_id',
        'leave_rule_id',
        'start_date',
        'end_date', 
        'year',
        'total_days', 
        'leave_session', 
        'reason', 
        'attachment',
        'is_approve',
        'is_approve_hr',
        'status',
    ];

    public function leaveRule()
    {
        return $this->belongsTo(LeaveRule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
