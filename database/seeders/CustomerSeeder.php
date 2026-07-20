<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => '测试用户',
                'phone' => '13800138000',
                'password' => 'password',
            ]
        );

        Customer::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => '普通用户',
                'phone' => '13900139000',
                'password' => 'password',
            ]
        );
    }
}
