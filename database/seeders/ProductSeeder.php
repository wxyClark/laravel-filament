<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'type' => ProductType::ENTITY->value,
                'status' => ProductStatus::ACTIVE->value,
                'description' => 'Apple 最新款旗舰手机',
            ],
            [
                'name' => 'MacBook Pro 14',
                'type' => ProductType::ENTITY->value,
                'status' => ProductStatus::ACTIVE->value,
                'description' => 'Apple 专业级笔记本电脑',
            ],
            [
                'name' => 'AirPods Pro 2',
                'type' => ProductType::ENTITY->value,
                'status' => ProductStatus::ACTIVE->value,
                'description' => 'Apple 无线降噪耳机',
            ],
            [
                'name' => '在线课程 - PHP 高级开发',
                'type' => ProductType::VIRTUAL->value,
                'status' => ProductStatus::ACTIVE->value,
                'description' => 'PHP 高级开发视频课程',
            ],
            [
                'name' => '软件许可证 - PhpStorm',
                'type' => ProductType::VIRTUAL->value,
                'status' => ProductStatus::INACTIVE->value,
                'description' => 'PhpStorm IDE 年度许可证',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
