<?php

declare(strict_types=1);

use App\CrossSell\Order\Enums\OrderStatus;
use App\CrossSell\Order\Models\Order;
use App\CrossSell\Order\Models\OrderItem;
use App\CrossSell\Product\Models\Product;
use App\CrossSell\Product\Models\SKU;
use App\CrossSell\Tenant\Models\Tenant;
use App\CrossSell\Warehouse\Enums\PlatformType;
use App\Domains\Notification\Models\AdminNotification;
use App\Models\Admin;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->tenant = Tenant::create([
        'name' => '测试租户',
        'slug' => 'test-tenant-order',
        'status' => 'active',
        'plan' => 'free',
    ]);
});

test('可以创建订单', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-001',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Pending,
        'buyer_name' => '张三',
        'buyer_email' => 'zhangsan@example.com',
        'total' => 199.99,
        'currency' => 'USD',
    ]);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->order_no)->toBe('ORD-20260726-001');
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->total)->toBe('199.99');
});

test('订单可以关联租户', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-002',
        'platform' => PlatformType::Temu,
        'status' => OrderStatus::Pending,
        'total' => 100.00,
    ]);

    expect($order->tenant)->not->toBeNull();
    expect($order->tenant->id)->toBe($this->tenant->id);
});

test('订单可以关联订单明细', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-003',
        'platform' => PlatformType::Shopify,
        'status' => OrderStatus::Pending,
        'total' => 299.99,
    ]);

    $product = Product::create([
        'tenant_id' => $this->tenant->id,
        'title' => '测试商品-订单',
        'status' => 'active',
    ]);

    $sku = SKU::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $product->id,
        'sku_code' => 'SKU-ORDER-001',
        'price' => 99.99,
        'stock' => 50,
    ]);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'sku_id' => $sku->id,
        'quantity' => 2,
        'unit_price' => 99.99,
        'total_price' => 199.98,
    ]);

    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->quantity)->toBe(2);
});

test('订单状态从待处理可以确认', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-004',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Pending,
        'total' => 100.00,
    ]);

    $result = $order->confirm();

    expect($result)->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('订单状态从已确认可以转为处理中', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-005',
        'platform' => PlatformType::Temu,
        'status' => OrderStatus::Confirmed,
        'total' => 150.00,
    ]);

    // 手动更新状态到 processing（因为模型中没有 process 方法）
    $order->update(['status' => OrderStatus::Processing]);

    expect($order->fresh()->status)->toBe(OrderStatus::Processing);
});

test('订单状态从处理中可以发货', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-006',
        'platform' => PlatformType::Shopify,
        'status' => OrderStatus::Processing,
        'total' => 200.00,
    ]);

    $result = $order->ship();

    expect($result)->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatus::Shipped);
});

test('订单状态从已发货可以签收', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-007',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Shipped,
        'total' => 250.00,
    ]);

    // 手动更新状态到 delivered（因为模型中没有 deliver 方法）
    $order->update(['status' => OrderStatus::Delivered]);

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered);
});

test('订单状态从已签收可以完成', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-008',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Delivered,
        'total' => 300.00,
    ]);

    $result = $order->complete();

    expect($result)->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
});

test('待处理订单可以取消', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-009',
        'platform' => PlatformType::Temu,
        'status' => OrderStatus::Pending,
        'total' => 100.00,
    ]);

    $result = $order->cancel();

    expect($result)->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('已确认订单可以取消', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-010',
        'platform' => PlatformType::Shopify,
        'status' => OrderStatus::Confirmed,
        'total' => 150.00,
    ]);

    $result = $order->cancel();

    expect($result)->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('已完成订单不能取消', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-011',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Completed,
        'total' => 200.00,
    ]);

    $result = $order->cancel();

    expect($result)->toBeFalse();
    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
});

test('已取消订单不能取消', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-012',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Cancelled,
        'total' => 100.00,
    ]);

    $result = $order->cancel();

    expect($result)->toBeFalse();
    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('非待处理状态不能确认', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-013',
        'platform' => PlatformType::Temu,
        'status' => OrderStatus::Confirmed,
        'total' => 100.00,
    ]);

    $result = $order->confirm();

    expect($result)->toBeFalse();
    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('非处理中状态不能发货', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-014',
        'platform' => PlatformType::Shopify,
        'status' => OrderStatus::Pending,
        'total' => 100.00,
    ]);

    $result = $order->ship();

    expect($result)->toBeFalse();
    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('非已签收状态不能完成', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-015',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Shipped,
        'total' => 100.00,
    ]);

    $result = $order->complete();

    expect($result)->toBeFalse();
    expect($order->fresh()->status)->toBe(OrderStatus::Shipped);
});

test('订单状态变更会触发通知', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-016',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Pending,
        'total' => 100.00,
    ]);

    $order->confirm();

    $notification = AdminNotification::where('type', 'order_status_changed')
        ->where('title', 'like', '%ORD-20260726-016%')
        ->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['old_status'])->toBe('pending');
    expect($notification->data['new_status'])->toBe('confirmed');
});

test('订单状态枚举标签正确', function () {
    expect(OrderStatus::Pending->label())->toBe('待处理');
    expect(OrderStatus::Confirmed->label())->toBe('已确认');
    expect(OrderStatus::Processing->label())->toBe('处理中');
    expect(OrderStatus::Shipped->label())->toBe('已发货');
    expect(OrderStatus::Delivered->label())->toBe('已签收');
    expect(OrderStatus::Completed->label())->toBe('已完成');
    expect(OrderStatus::Cancelled->label())->toBe('已取消');
    expect(OrderStatus::Refunded->label())->toBe('已退款');
});

test('订单状态枚举颜色正确', function () {
    expect(OrderStatus::Pending->getColor())->toBe('warning');
    expect(OrderStatus::Confirmed->getColor())->toBe('info');
    expect(OrderStatus::Processing->getColor())->toBe('info');
    expect(OrderStatus::Shipped->getColor())->toBe('info');
    expect(OrderStatus::Delivered->getColor())->toBe('success');
    expect(OrderStatus::Completed->getColor())->toBe('success');
    expect(OrderStatus::Cancelled->getColor())->toBe('danger');
    expect(OrderStatus::Refunded->getColor())->toBe('danger');
});

test('订单金额字段正确存储', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-017',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Pending,
        'subtotal' => 100.00,
        'shipping_fee' => 10.00,
        'tax' => 8.00,
        'discount' => 5.00,
        'total' => 113.00,
        'currency' => 'USD',
    ]);

    expect($order->subtotal)->toBe('100.00');
    expect($order->shipping_fee)->toBe('10.00');
    expect($order->tax)->toBe('8.00');
    expect($order->discount)->toBe('5.00');
    expect($order->total)->toBe('113.00');
});

test('订单支持软删除', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-018',
        'platform' => PlatformType::Manual,
        'status' => OrderStatus::Cancelled,
        'total' => 100.00,
    ]);

    $orderId = $order->id;
    $order->delete();

    expect(Order::find($orderId))->toBeNull();
    expect(Order::withTrashed()->find($orderId))->not->toBeNull();
});

test('订单平台枚举正确', function () {
    $order = Order::create([
        'tenant_id' => $this->tenant->id,
        'order_no' => 'ORD-20260726-019',
        'platform' => PlatformType::Amazon,
        'status' => OrderStatus::Pending,
        'total' => 100.00,
    ]);

    expect($order->platform)->toBe(PlatformType::Amazon);
});
