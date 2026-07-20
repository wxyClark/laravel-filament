<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
            ]
        );

        // Web 端用户（用于 http://localhost:8082/login 登录）
        $this->call([
            CustomerSeeder::class,
        ]);

        // 商品测试数据
        $this->call([
            ProductSeeder::class,
        ]);

        // 只在地址表为空时执行 AddressSeeder（避免重复插入）
        $hasAddresses = DB::table('addresses')->exists();

        if (! $hasAddresses) {
            $this->call([
                AddressSeeder::class,
            ]);
        }

        // 接口测试数据（幂等，可重复执行）
        $this->call([
            ApiTestingSeeder::class,
        ]);
    }
}
