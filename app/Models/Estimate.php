<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    protected $primaryKey = 'estimate_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'estimate_id',
        'client_id',
        'issue_date',
        'due_date',
        'currency',
        'status',
        'sub_total',
        'grand_total',
        'terms',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
    
    public function items()
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id', 'estimate_id');
    }
}
