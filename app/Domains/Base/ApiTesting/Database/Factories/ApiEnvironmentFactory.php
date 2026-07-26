<?php

declare(strict_types=1);

namespace App\Domains\Base\ApiTesting\Database\Factories;

use App\Domains\Base\ApiTesting\Enums\AuthType;
use App\Domains\Base\ApiTesting\Models\ApiEnvironment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApiEnvironmentFactory extends Factory
{
    protected $model = ApiEnvironment::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'base_url' => fake()->url(),
            'auth_type' => AuthType::NONE->value,
            'auth_config' => null,
            'headers' => null,
            'is_default' => false,
            'sort_order' => 0,
        ];
    }
}
