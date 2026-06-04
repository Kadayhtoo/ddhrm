<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    protected $table = 'estimate_items';

    protected $fillable = [
        'estimate_id',
        'name',
        'quantity',
        'price',
        'total',
        'item_type',
        'description',
    ];
}
