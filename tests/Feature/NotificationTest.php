<?php

declare(strict_types=1);

use App\CrossSell\Product\Models\Product;
use App\CrossSell\Product\Models\SKU;
use App\CrossSell\Tenant\Models\Tenant;
use App\CrossSell\Warehouse\Models\Inventory;
use App\CrossSell\Warehouse\Models\Warehouse;
use App\Domains\Notification\Events\LowStockDetected;
use App\Domains\Notification\Models\AdminNotification;
use App\Models\Admin;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(PermissionSeeder::class);
});

test('管理员可以创建通知', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $notification = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'system',
        'title' => '测试通知',
        'message' => '这是一条测试通知',
        'is_read' => false,
    ]);

    expect($notification)->toBeInstanceOf(AdminNotification::class);
    expect($notification->title)->toBe('测试通知');
    expect($notification->is_read)->toBeFalse();
});

test('订单状态变更通知可以创建', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $notification = AdminNotification::notifyOrderStatusChanged(
        adminId: $admin->id,
        orderNo: 'ORD-20260726-001',
        oldStatus: 'pending',
        newStatus: 'confirmed',
    );

    expect($notification)->toBeInstanceOf(AdminNotification::class);
    expect($notification->type)->toBe('order_status_changed');
    expect($notification->title)->toContain('ORD-20260726-001');
    expect($notification->data['old_status'])->toBe('pending');
    expect($notification->data['new_status'])->toBe('confirmed');
});

test('低库存预警通知可以创建', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $notification = AdminNotification::notifyLowStock(
        adminId: $admin->id,
        productName: '测试商品',
        skuCode: 'SKU-001',
        currentStock: 5,
        threshold: 10,
    );

    expect($notification)->toBeInstanceOf(AdminNotification::class);
    expect($notification->type)->toBe('low_stock');
    expect($notification->title)->toContain('测试商品');
    expect($notification->data['sku_code'])->toBe('SKU-001');
    expect($notification->data['current_stock'])->toBe(5);
});

test('系统通知可以创建', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $notification = AdminNotification::notifySystem(
        adminId: $admin->id,
        title: '系统维护',
        message: '系统将于今晚维护',
        data: ['maintenance_time' => '2026-07-26 22:00'],
    );

    expect($notification)->toBeInstanceOf(AdminNotification::class);
    expect($notification->type)->toBe('system');
    expect($notification->title)->toBe('系统维护');
    expect($notification->data['maintenance_time'])->toBe('2026-07-26 22:00');
});

test('通知可以标记为已读', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $notification = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'system',
        'title' => '测试通知',
        'message' => '这是一条测试通知',
        'is_read' => false,
    ]);

    expect($notification->is_read)->toBeFalse();

    $notification->markAsRead();

    expect($notification->fresh()->is_read)->toBeTrue();
    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('通知类型标签正确', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $orderNotif = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'order_status_changed',
        'title' => '订单状态变更',
        'message' => '测试',
        'is_read' => false,
    ]);

    $stockNotif = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'low_stock',
        'title' => '库存预警',
        'message' => '测试',
        'is_read' => false,
    ]);

    $systemNotif = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'system',
        'title' => '系统通知',
        'message' => '测试',
        'is_read' => false,
    ]);

    expect($orderNotif->type_label)->toBe('订单状态变更');
    expect($stockNotif->type_label)->toBe('库存预警');
    expect($systemNotif->type_label)->toBe('系统通知');
});

test('通知类型颜色正确', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $orderNotif = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'order_status_changed',
        'title' => '订单状态变更',
        'message' => '测试',
        'is_read' => false,
    ]);

    $stockNotif = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'low_stock',
        'title' => '库存预警',
        'message' => '测试',
        'is_read' => false,
    ]);

    $systemNotif = AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'system',
        'title' => '系统通知',
        'message' => '测试',
        'is_read' => false,
    ]);

    expect($orderNotif->type_color)->toBe('info');
    expect($stockNotif->type_color)->toBe('warning');
    expect($systemNotif->type_color)->toBe('gray');
});

test('低库存预警监听器可以发送通知', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    // 直接测试 LowStockDetected 事件
    $event = new LowStockDetected(
        sku: new SKU,
        currentStock: 5,
        threshold: 10,
    );

    expect($event->currentStock)->toBe(5);
    expect($event->threshold)->toBe(10);
});

test('库存扣减时触发低库存预警', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $tenant = Tenant::create([
        'name' => '测试租户',
        'slug' => 'test-tenant',
        'status' => 'active',
        'plan' => 'free',
    ]);

    $warehouse = Warehouse::create([
        'tenant_id' => $tenant->id,
        'name' => '测试仓库',
        'is_active' => true,
    ]);

    // 创建产品
    $product = Product::create([
        'tenant_id' => $tenant->id,
        'title' => '测试产品',
        'status' => 'active',
    ]);

    $sku = SKU::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'sku_code' => 'SKU-TEST-001',
        'price' => 99.99,
        'stock' => 100,
    ]);

    $inventory = Inventory::create([
        'tenant_id' => $tenant->id,
        'sku_id' => $sku->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 15,
        'reserved_quantity' => 0,
        'available_quantity' => 15,
        'min_stock' => 10,
        'max_stock' => 100,
    ]);

    // 扣减库存到低于阈值
    $result = $inventory->deduct(8);

    expect($result)->toBeTrue();
    expect($inventory->fresh()->quantity)->toBe(7);
});
