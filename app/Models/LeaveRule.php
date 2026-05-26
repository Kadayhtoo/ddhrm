<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRule extends Model
{
    protected $fillable = [
        'name',
        'type',
        'days',
    ];

    protected static function booted()
    {
        static::updated(function ($leaveRule) {
            if ($leaveRule->wasChanged('days')) {
                $balances = LeaveBalance::where('leave_rule_id', $leaveRule->id)->get();

                foreach ($balances as $balance) {
                    $balance->total_allowed_days = (float) $leaveRule->days;
                    $balance->remaining_days = $balance->total_allowed_days - $balance->used_days;
                    $balance->save();
                }
            }
        });
    }
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_rule_id');
    }
}
