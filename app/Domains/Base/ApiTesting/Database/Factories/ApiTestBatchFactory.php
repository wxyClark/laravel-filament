<?php

declare(strict_types=1);

namespace App\Domains\Base\ApiTesting\Database\Factories;

use App\Domains\Base\ApiTesting\Models\ApiTestBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApiTestBatchFactory extends Factory
{
    protected $model = ApiTestBatch::class;

    public function definition(): array
    {
        return [
            'test_case_ids' => [],
            'status' => 'pending',
        ];
    }
}
