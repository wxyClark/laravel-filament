<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'code' => fake()->unique()->numerify('######'),
            'level' => 'city',
            'level_num' => 2,
            'pinyin' => fake()->slug(1),
            'sort' => 0,
        ];
    }

    public function province(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'province',
            'level_num' => 1,
            'parent_id' => null,
        ]);
    }

    public function city(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'city',
            'level_num' => 2,
        ]);
    }

    public function district(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'district',
            'level_num' => 3,
        ]);
    }
}
