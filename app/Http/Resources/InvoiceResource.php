<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locations = config('location.countries'); 

        $client = $this->client;
        
        $countryName = $locations[$client->country]['name'] ?? $client->country;
        $cityName = $locations[$client->country]['cities'][$client->city]['name'] ?? $client->city;
        $townshipName = $locations[$client->country]['cities'][$client->city]['townships'][$client->township] ?? $client->township;
        
        return [
            'invoice_id' => $this->invoice_id,
            'client_id' => $this->client_id,
            'client_name' => $this->client ? trim($this->client->first_name . ' ' . $this->client->last_name) : 'Unknown Client',
            'client_email' => $this->client?->email,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'currency' => $this->currency,
            'discount_type' => $this->discount_type,
            'discount_value' => (float)$this->discount_value,
            'status' => $this->status,
            'sub_total' => (float)$this->sub_total,
            'grand_total' => (float)$this->grand_total,
            'terms' => $this->terms,
            'payment_attachment' => $this->payment_attachment,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'items' => $this->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => (float)$item->price,
                    'total' => (float)$item->total,
                    'item_type' => $item->item_type,
                    'description' => $item->description,
                ];
            }),
            'client' => [
            'address' => $client->address,
            'country_name' => $countryName,
            'city_name' => $cityName,
            'township_name' => $townshipName,
        ],
        ];
    }
}