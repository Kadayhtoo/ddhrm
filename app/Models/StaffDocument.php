<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDocument extends Model
{    
    protected $fillable = [
        'staff_id',
        'document_type',
        'file_path',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
