<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'country',
        'city',
        'township',
        'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function contacts() 
    {
        return $this->hasMany(ContactPerson::class); 
    }
}
