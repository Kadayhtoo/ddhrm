<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_id'     => $this->faker->unique()->numerify('2026-######'),
            'client_id'      => Client::factory(), 
            'issue_date'     => now()->toDateString(),
            'due_date'       => now()->addDays(7)->toDateString(),
            'currency'       => 'USD',
            'discount_type'  => 'fixed',
            'discount_value' => 0,
            'status'         => 'draft',
            'sub_total'      => 100.00,
            'grand_total'    => 100.00,
            'terms'          => 'Please send payment within 7 days.',
        ];
    }
}