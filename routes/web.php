<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

// 根据域名加载不同的路由文件
$host = request()->getHost();

if (str_contains($host, 'shop.')) {
    // 商城前端路由
    require __DIR__.'/shop.php';
} elseif (str_contains($host, 'admin.')) {
    // 管理后台路由
    require __DIR__.'/admin.php';
} elseif (str_contains($host, 'hr.')) {
    // 薪福通路由
    require __DIR__.'/xinfutong.php';
} elseif (str_contains($host, 'super.')) {
    // 权限管理路由
    require __DIR__.'/permission.php';
} else {
    // 默认路由
    Route::get('/', function () {
        return response()->json([
            'message' => 'CrossSell SaaS Platform',
            'domains' => [
                'shop.example.com' => '商城前端',
                'admin.example.com' => '管理后台',
                'hr.example.com' => '薪福通',
                'super.example.com' => '权限管理',
            ],
        ]);
    });
}
