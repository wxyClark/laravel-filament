<?php

declare(strict_types=1);

use App\CrossSell\Product\Models\Product;
use App\CrossSell\Product\Models\SKU;
use App\CrossSell\Tenant\Models\Tenant;
use App\CrossSell\Warehouse\Models\Inventory;
use App\CrossSell\Warehouse\Models\Warehouse;
use App\Domains\Notification\Models\AdminNotification;
use App\Models\Admin;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->tenant = Tenant::create([
        'name' => '测试租户',
        'slug' => 'test-tenant-inventory',
        'status' => 'active',
        'plan' => 'free',
    ]);

    $this->warehouse = Warehouse::create([
        'tenant_id' => $this->tenant->id,
        'name' => '测试仓库',
        'is_active' => true,
    ]);

    $this->product = Product::create([
        'tenant_id' => $this->tenant->id,
        'title' => '测试产品',
        'status' => 'active',
    ]);

    $this->sku = SKU::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'sku_code' => 'SKU-INV-001',
        'price' => 99.99,
        'stock' => 100,
    ]);
});

test('可以创建库存记录', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
        'available_quantity' => 100,
        'min_stock' => 10,
        'max_stock' => 500,
    ]);

    expect($inventory)->toBeInstanceOf(Inventory::class);
    expect($inventory->quantity)->toBe(100);
    expect($inventory->available_quantity)->toBe(100);
});

test('库存可以关联租户', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'available_quantity' => 50,
        'min_stock' => 10,
        'max_stock' => 200,
    ]);

    expect($inventory->tenant)->not->toBeNull();
    expect($inventory->tenant->id)->toBe($this->tenant->id);
});

test('库存可以关联SKU', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'available_quantity' => 50,
        'min_stock' => 10,
        'max_stock' => 200,
    ]);

    expect($inventory->sku)->not->toBeNull();
    expect($inventory->sku->sku_code)->toBe('SKU-INV-001');
});

test('库存可以关联仓库', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'available_quantity' => 50,
        'min_stock' => 10,
        'max_stock' => 200,
    ]);

    expect($inventory->warehouse)->not->toBeNull();
    expect($inventory->warehouse->name)->toBe('测试仓库');
});

test('库存可以正常扣减', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
        'available_quantity' => 100,
        'min_stock' => 10,
        'max_stock' => 500,
    ]);

    $result = $inventory->deduct(30);

    expect($result)->toBeTrue();
    expect($inventory->fresh()->quantity)->toBe(70);
    expect($inventory->fresh()->available_quantity)->toBe(70);
});

test('库存不足时扣减失败', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 10,
        'reserved_quantity' => 0,
        'available_quantity' => 10,
        'min_stock' => 5,
        'max_stock' => 100,
    ]);

    $result = $inventory->deduct(20);

    expect($result)->toBeFalse();
    expect($inventory->fresh()->quantity)->toBe(10);
    expect($inventory->fresh()->available_quantity)->toBe(10);
});

test('库存可以恢复', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'available_quantity' => 50,
        'min_stock' => 10,
        'max_stock' => 200,
    ]);

    $result = $inventory->restore(20);

    expect($result)->toBeTrue();
    expect($inventory->fresh()->quantity)->toBe(70);
    expect($inventory->fresh()->available_quantity)->toBe(70);
});

test('低库存检测正确', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 5,
        'reserved_quantity' => 0,
        'available_quantity' => 5,
        'min_stock' => 10,
        'max_stock' => 100,
    ]);

    expect($inventory->isLowStock())->toBeTrue();
});

test('正常库存不是低库存', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'available_quantity' => 50,
        'min_stock' => 10,
        'max_stock' => 100,
    ]);

    expect($inventory->isLowStock())->toBeFalse();
});

test('扣减到低库存阈值时触发预警', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 15,
        'reserved_quantity' => 0,
        'available_quantity' => 15,
        'min_stock' => 10,
        'max_stock' => 100,
    ]);

    // 扣减 8 个，剩余 7 个，低于阈值 10
    $inventory->deduct(8);

    $notification = AdminNotification::where('admin_id', $admin->id)
        ->where('type', 'low_stock')
        ->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['current_stock'])->toBe(7);
    expect($notification->data['threshold'])->toBe(10);
});

test('扣减后未低于阈值不触发预警', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'available_quantity' => 50,
        'min_stock' => 10,
        'max_stock' => 100,
    ]);

    // 扣减 5 个，剩余 45 个，高于阈值 10
    $inventory->deduct(5);

    $notification = AdminNotification::where('admin_id', $admin->id)
        ->where('type', 'low_stock')
        ->first();

    expect($notification)->toBeNull();
});

test('库存数量字段正确存储', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 10,
        'available_quantity' => 90,
        'min_stock' => 20,
        'max_stock' => 500,
    ]);

    expect($inventory->quantity)->toBe(100);
    expect($inventory->reserved_quantity)->toBe(10);
    expect($inventory->available_quantity)->toBe(90);
    expect($inventory->min_stock)->toBe(20);
    expect($inventory->max_stock)->toBe(500);
});

test('库存可以批量扣减', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
        'available_quantity' => 100,
        'min_stock' => 10,
        'max_stock' => 500,
    ]);

    // 连续扣减
    $inventory->deduct(20);
    $inventory->deduct(30);
    $inventory->deduct(10);

    expect($inventory->fresh()->quantity)->toBe(40);
    expect($inventory->fresh()->available_quantity)->toBe(40);
});

test('库存可以批量恢复', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
        'reserved_quantity' => 0,
        'available_quantity' => 50,
        'min_stock' => 10,
        'max_stock' => 200,
    ]);

    // 连续恢复
    $inventory->restore(10);
    $inventory->restore(20);
    $inventory->restore(5);

    expect($inventory->fresh()->quantity)->toBe(85);
    expect($inventory->fresh()->available_quantity)->toBe(85);
});

test('同一SKU可以有多仓库库存', function () {
    $warehouse2 = Warehouse::create([
        'tenant_id' => $this->tenant->id,
        'name' => '仓库2',
        'is_active' => true,
    ]);

    $inventory1 = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
        'available_quantity' => 100,
        'min_stock' => 10,
        'max_stock' => 500,
    ]);

    $inventory2 = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $warehouse2->id,
        'quantity' => 200,
        'reserved_quantity' => 0,
        'available_quantity' => 200,
        'min_stock' => 20,
        'max_stock' => 1000,
    ]);

    expect($inventory1->quantity)->toBe(100);
    expect($inventory2->quantity)->toBe(200);

    // 扣减不影响其他仓库
    $inventory1->deduct(50);

    expect($inventory1->fresh()->quantity)->toBe(50);
    expect($inventory2->fresh()->quantity)->toBe(200);
});

test('低库存阈值可以为0', function () {
    $inventory = Inventory::create([
        'tenant_id' => $this->tenant->id,
        'sku_id' => $this->sku->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 1,
        'reserved_quantity' => 0,
        'available_quantity' => 1,
        'min_stock' => 0,
        'max_stock' => 100,
    ]);

    expect($inventory->isLowStock())->toBeFalse();

    // 扣减到 0 时触发预警
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');

    $inventory->deduct(1);

    expect($inventory->fresh()->quantity)->toBe(0);
    expect($inventory->fresh()->isLowStock())->toBeTrue();
});
