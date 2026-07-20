<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Database\Factories;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category_id' => null,
            'type' => fake()->randomElement([ProductType::ENTITY, ProductType::VIRTUAL])->value,
            'status' => ProductStatus::ACTIVE->value,
            'description' => fake()->sentence(),
        ];
    }
}
