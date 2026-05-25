<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'leave_rule_id', 
        'total_allowed_days', 
        'used_days', 
        'remaining_days'
    ];

    public function leaveRule()
    {
        return $this->belongsTo(LeaveRule::class);
    }
}
