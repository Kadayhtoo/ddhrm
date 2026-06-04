<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'address'    => $this->address,
            'country'    => $this->country,
            'city'       => $this->city,
            'township'   => $this->township,
            'social_links' => $this->social_links ?? [],
            'contacts'   => $this->whenLoaded('contacts'), 
        ];
    }
}