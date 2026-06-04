<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutUsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'description' => $this->description,
            'address' => $this->address,
            'township' => $this->township,
            'city' => $this->city,
            'country' => $this->country,
            'website' => $this->website,
            'phone_numbers' => $this->phone_numbers ?? [],
            'email_addresses' => $this->email_addresses ?? [],
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
