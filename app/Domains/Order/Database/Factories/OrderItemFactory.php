<?php

declare(strict_types=1);

namespace App\Domains\Order\Database\Factories;

use App\Domains\Catalog\Models\Sku;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'sku_id' => Sku::factory(),
            'product_id' => null,
            'qty' => fake()->numberBetween(1, 3),
            'unit_price' => fake()->randomFloat(2, 1, 999),
            'subtotal' => fake()->randomFloat(2, 1, 999),
        ];
    }
}
