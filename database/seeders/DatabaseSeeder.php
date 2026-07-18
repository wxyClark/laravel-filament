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
                'password' => bcrypt('password'),
            ]
        );

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
