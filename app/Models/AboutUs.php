<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    protected $table = 'about_us';

    protected $fillable = [
        'company_name',
        'description',
        'address',
        'township',
        'city',
        'country',
        'website',
        'phone_numbers',
        'email_addresses',
        'logo_path',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'email_addresses' => 'array',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return asset('storage/' . $this->logo_path);
    }
}
