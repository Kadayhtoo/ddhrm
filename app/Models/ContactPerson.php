<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'email',
        'phone',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
