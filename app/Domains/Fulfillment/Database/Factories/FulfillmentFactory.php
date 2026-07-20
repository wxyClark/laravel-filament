<?php

declare(strict_types=1);

namespace App\Domains\Fulfillment\Database\Factories;

use App\Domains\Fulfillment\Enums\FulfillmentType;
use App\Domains\Fulfillment\Models\Fulfillment;
use App\Domains\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class FulfillmentFactory extends Factory
{
    protected $model = Fulfillment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => fake()->randomElement([FulfillmentType::ENTITY, FulfillmentType::VIRTUAL])->value,
            'status' => 'pending',
        ];
    }
}
