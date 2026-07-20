<?php

declare(strict_types=1);

namespace App\Domains\Cart\Database\Factories;

use App\Domains\Cart\Models\Cart;
use App\Domains\Cart\Models\CartItem;
use App\Domains\Catalog\Models\Sku;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'sku_id' => Sku::factory(),
            'qty' => fake()->numberBetween(1, 5),
            'selected' => true,
        ];
    }
}
