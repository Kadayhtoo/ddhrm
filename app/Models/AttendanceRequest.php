<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_by',
        'reviewed_by',
        'type',
        'requested_date',
        'requested_clock_in_at',
        'requested_clock_out_at',
        'requested_status',
        'reason',
        'status',
        'reviewed_at',
        'reviewer_note',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'requested_clock_in_at' => 'datetime',
            'requested_clock_out_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
