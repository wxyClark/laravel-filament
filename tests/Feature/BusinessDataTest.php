<?php

declare(strict_types=1);

use App\CrossSell\AfterSale\Models\AfterSale;
use App\CrossSell\Fulfillment\Models\CoreFulfillment as Fulfillment;
use App\CrossSell\Fulfillment\Models\Shipment;
use App\CrossSell\Fulfillment\Models\ShipmentItem;
use App\CrossSell\Order\Models\Order;
use App\CrossSell\Order\Models\OrderItem;
use App\CrossSell\Order\Models\Refund;
use App\CrossSell\Order\Models\RefundItem;
use App\CrossSell\Payment\Models\PaymentRecord;
use App\CrossSell\Payment\Models\Wallet;
use App\CrossSell\Product\Models\Product;
use App\CrossSell\Product\Models\ProductCategory;
use App\CrossSell\Product\Models\ProductTranslation;
use App\CrossSell\Product\Models\SKU;
use App\CrossSell\Product\Models\SKUTranslation;
use App\CrossSell\Tenant\Models\Tenant;
use App\CrossSell\Warehouse\Models\Inventory;
use App\CrossSell\Warehouse\Models\LogisticsChannel;
use App\CrossSell\Warehouse\Models\Warehouse;
use App\Domains\Base\Logging\Models\AdminAuditLog;
use App\Domains\Base\Logging\Models\BusinessLog;
use App\Domains\Base\Logging\Models\RequestLog;
use App\Domains\Notification\Models\AdminNotification;
use App\Models\Admin;
use App\Models\Customer;
use Database\Seeders\PermissionSeeder;

/**
 * 全量业务数据测试
 * 为每个数据表生成符合业务逻辑的测试数据
 */
beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('完整业务流程：创建租户到订单完成', function () {
    // 1. 创建管理员
    $admin = Admin::factory()->create([
        'name' => '系统管理员',
        'email' => 'admin@crosssell.com',
    ]);
    $admin->assignRole('super_admin');

    // 2. 创建租户
    $tenant = Tenant::create([
        'name' => '深圳跨境电商有限公司',
        'slug' => 'sz-crosssell',
        'email' => 'contact@szcrosssell.com',
        'phone' => '13800138000',
        'status' => 'active',
        'plan' => 'pro',
        'trial_ends_at' => now()->addDays(30),
    ]);

    // 3. 创建仓库
    $warehouse1 = Warehouse::create([
        'tenant_id' => $tenant->id,
        'name' => '义乌主仓库',
        'code' => 'YWI-001',
        'address' => '浙江省义乌市国际商贸城',
        'country' => 'CN',
        'city' => '义乌市',
        'contact_name' => '张仓管',
        'contact_phone' => '13900139000',
        'is_active' => true,
        'is_default' => true,
    ]);

    $warehouse2 = Warehouse::create([
        'tenant_id' => $tenant->id,
        'name' => '深圳分仓',
        'code' => 'SZ-001',
        'address' => '广东省深圳市南山区科技园',
        'country' => 'CN',
        'city' => '深圳市',
        'contact_name' => '李仓管',
        'contact_phone' => '13700137000',
        'is_active' => true,
        'is_default' => false,
    ]);

    // 4. 创建商品分类
    $categoryElectronics = ProductCategory::create([
        'tenant_id' => $tenant->id,
        'name' => '电子产品',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $categoryClothing = ProductCategory::create([
        'tenant_id' => $tenant->id,
        'name' => '服装',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    // 5. 创建商品
    $product1 = Product::create([
        'tenant_id' => $tenant->id,
        'title' => '无线蓝牙耳机 Pro',
        'brand' => 'SoundMax',
        'category_id' => $categoryElectronics->id,
        'status' => 'active',
        'description' => '高品质无线蓝牙耳机，支持降噪',
        'main_image' => 'products/headphone-pro.jpg',
        'images' => ['products/headphone-pro-1.jpg', 'products/headphone-pro-2.jpg'],
        'source_url' => 'https://1688.com/item/123456.html',
        'source_id' => '123456',
        'tags' => ['蓝牙', '耳机', '降噪'],
    ]);

    $product2 = Product::create([
        'tenant_id' => $tenant->id,
        'title' => '纯棉T恤 男女同款',
        'brand' => 'ComfortWear',
        'category_id' => $categoryClothing->id,
        'status' => 'active',
        'description' => '100%纯棉，舒适透气',
        'main_image' => 'products/tshirt-white.jpg',
        'source_url' => 'https://1688.com/item/789012.html',
        'source_id' => '789012',
        'tags' => ['纯棉', 'T恤', '休闲'],
    ]);

    // 6. 创建商品翻译
    ProductTranslation::create([
        'product_id' => $product1->id,
        'language' => 'en',
        'title' => 'Wireless Bluetooth Headphones Pro',
        'description' => 'High-quality wireless Bluetooth headphones with noise cancellation',
    ]);

    ProductTranslation::create([
        'product_id' => $product2->id,
        'language' => 'en',
        'title' => 'Pure Cotton T-Shirt Unisex',
        'description' => '100% pure cotton, comfortable and breathable',
    ]);

    // 7. 创建SKU
    $sku1 = SKU::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product1->id,
        'sku_code' => 'SM-HP-PRO-BLK',
        'price' => 299.99,
        'stock' => 500,
        'attributes' => ['color' => '黑色', 'version' => '标准版'],
    ]);

    $sku2 = SKU::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product1->id,
        'sku_code' => 'SM-HP-PRO-WHT',
        'price' => 299.99,
        'stock' => 300,
        'attributes' => ['color' => '白色', 'version' => '标准版'],
    ]);

    $sku3 = SKU::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product2->id,
        'sku_code' => 'CW-TS-WHT-M',
        'price' => 49.99,
        'stock' => 1000,
        'attributes' => ['color' => '白色', 'size' => 'M'],
    ]);

    // 8. 创建SKU翻译
    SkuTranslation::create([
        'sku_id' => $sku1->id,
        'language' => 'en',
        'name' => 'Wireless Bluetooth Headphones Pro - Black',
    ]);

    // 9. 创建库存
    Inventory::create([
        'tenant_id' => $tenant->id,
        'sku_id' => $sku1->id,
        'warehouse_id' => $warehouse1->id,
        'quantity' => 300,
        'reserved_quantity' => 10,
        'available_quantity' => 290,
        'min_stock' => 50,
        'max_stock' => 1000,
    ]);

    Inventory::create([
        'tenant_id' => $tenant->id,
        'sku_id' => $sku1->id,
        'warehouse_id' => $warehouse2->id,
        'quantity' => 200,
        'reserved_quantity' => 5,
        'available_quantity' => 195,
        'min_stock' => 30,
        'max_stock' => 500,
    ]);

    Inventory::create([
        'tenant_id' => $tenant->id,
        'sku_id' => $sku3->id,
        'warehouse_id' => $warehouse1->id,
        'quantity' => 800,
        'reserved_quantity' => 0,
        'available_quantity' => 800,
        'min_stock' => 100,
        'max_stock' => 2000,
    ]);

    // 10. 创建客户
    $customer1 = Customer::create([
        'name' => '王小明',
        'email' => 'wangxm@example.com',
        'phone' => '13600136001',
        'password' => 'password',
    ]);

    $customer2 = Customer::create([
        'name' => 'John Smith',
        'email' => 'john.smith@example.com',
        'phone' => '+1234567890',
        'password' => 'password',
    ]);

    // 11. 创建订单
    $order1 = Order::create([
        'tenant_id' => $tenant->id,
        'order_no' => 'ORD-20260726-001',
        'platform' => 'temu',
        'status' => 'completed',
        'buyer_name' => '王小明',
        'buyer_email' => 'wangxm@example.com',
        'buyer_phone' => '13600136001',
        'shipping_name' => '王小明',
        'shipping_phone' => '13600136001',
        'shipping_address' => [
            'country' => 'CN',
            'province' => '浙江省',
            'city' => '杭州市',
            'district' => '西湖区',
            'address' => '文三路123号',
        ],
        'shipping_country' => 'CN',
        'subtotal' => 299.99,
        'shipping_fee' => 15.00,
        'tax' => 0.00,
        'discount' => 0.00,
        'total' => 314.99,
        'currency' => 'CNY',
        'note' => '请尽快发货',
        'customer_note' => '周末再送',
    ]);

    $order2 = Order::create([
        'tenant_id' => $tenant->id,
        'order_no' => 'ORD-20260726-002',
        'platform' => 'shopify',
        'status' => 'shipped',
        'buyer_name' => 'John Smith',
        'buyer_email' => 'john.smith@example.com',
        'shipping_name' => 'John Smith',
        'shipping_address' => [
            'country' => 'US',
            'state' => 'California',
            'city' => 'Los Angeles',
            'address' => '123 Main St',
        ],
        'shipping_country' => 'US',
        'subtotal' => 49.99,
        'shipping_fee' => 5.00,
        'total' => 54.99,
        'currency' => 'USD',
    ]);

    // 12. 创建订单明细
    OrderItem::create([
        'order_id' => $order1->id,
        'sku_id' => $sku1->id,
        'product_title' => '无线蓝牙耳机 Pro',
        'sku_code' => 'SM-HP-PRO-BLK',
        'quantity' => 1,
        'unit_price' => 299.99,
        'total_price' => 299.99,
    ]);

    OrderItem::create([
        'order_id' => $order2->id,
        'sku_id' => $sku3->id,
        'product_title' => '纯棉T恤 男女同款',
        'sku_code' => 'CW-TS-WHT-M',
        'quantity' => 1,
        'unit_price' => 49.99,
        'total_price' => 49.99,
    ]);

    // 13. 创建支付记录
    $wallet1 = Wallet::create([
        'user_id' => $customer1->id,
        'balance' => 500.00,
    ]);

    PaymentRecord::create([
        'user_id' => $customer1->id,
        'order_id' => $order1->id,
        'type' => 'pay',
        'amount' => 314.99,
        'gateway' => 'balance',
        'status' => 'success',
    ]);

    $wallet2 = Wallet::create([
        'user_id' => $customer2->id,
        'balance' => 200.00,
    ]);

    PaymentRecord::create([
        'user_id' => $customer2->id,
        'order_id' => $order2->id,
        'type' => 'pay',
        'amount' => 54.99,
        'gateway' => 'balance',
        'status' => 'success',
    ]);

    // 14. 创建物流渠道
    $logisticsChannel = LogisticsChannel::create([
        'tenant_id' => $tenant->id,
        'name' => '顺丰速运',
        'carrier' => 'SF',
        'code' => 'SF-Express',
        'supported_countries' => ['CN', 'US', 'JP'],
        'base_price' => 15.00,
        'price_per_kg' => 10.00,
        'estimated_days_min' => 3,
        'estimated_days_max' => 7,
        'is_active' => true,
    ]);

    // 15. 创建发货单
    $shipment1 = Shipment::create([
        'order_id' => $order1->id,
        'tenant_id' => $tenant->id,
        'warehouse_id' => $warehouse1->id,
        'logistics_channel_id' => $logisticsChannel->id,
        'shipment_no' => 'SH-20260726-001',
        'tracking_no' => 'SF1234567890',
        'carrier' => '顺丰速运',
        'status' => 'delivered',
        'shipped_at' => now()->subDays(3),
        'delivered_at' => now()->subDays(1),
    ]);

    ShipmentItem::create([
        'shipment_id' => $shipment1->id,
        'order_item_id' => $order1->items->first()->id,
        'quantity' => 1,
    ]);

    // 16. 创建履约记录
    Fulfillment::create([
        'order_id' => $order1->id,
        'type' => 'ship',
        'status' => 'completed',
    ]);

    // 16. 创建退款记录
    $refund1 = Refund::create([
        'order_id' => $order2->id,
        'tenant_id' => $tenant->id,
        'refund_no' => 'RF-20260726-001',
        'reason' => '尺寸不合适',
        'amount' => 54.99,
        'status' => 'pending',
    ]);

    RefundItem::create([
        'refund_id' => $refund1->id,
        'order_item_id' => $order2->items->first()->id,
        'quantity' => 1,
        'amount' => 49.99,
    ]);

    // 17. 创建售后记录
    AfterSale::create([
        'order_id' => $order2->id,
        'user_id' => $customer2->id,
        'type' => 'refund',
        'status' => 'pending',
        'refund_amount' => 54.99,
    ]);

    // 18. 创建审计日志
    AdminAuditLog::create([
        'admin_id' => $admin->id,
        'admin_name' => $admin->name,
        'admin_email' => $admin->email,
        'action' => 'created',
        'subject_type' => 'App\\Domains\\CrossSell\\Models\\Tenant',
        'subject_id' => $tenant->id,
        'new_values' => ['name' => $tenant->name],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'description' => "创建租户: {$tenant->name}",
    ]);

    AdminAuditLog::create([
        'admin_id' => $admin->id,
        'admin_name' => $admin->name,
        'admin_email' => $admin->email,
        'action' => 'login',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'description' => '管理员登录成功',
    ]);

    // 19. 创建通知
    AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'order_status_changed',
        'title' => "订单 {$order1->order_no} 状态变更",
        'message' => "订单 {$order1->order_no} 已完成",
        'data' => [
            'order_no' => $order1->order_no,
            'old_status' => 'shipped',
            'new_status' => 'completed',
        ],
        'is_read' => false,
    ]);

    AdminNotification::create([
        'admin_id' => $admin->id,
        'type' => 'low_stock',
        'title' => '库存预警: 无线蓝牙耳机 Pro',
        'message' => '商品库存低于预警阈值',
        'data' => [
            'product_name' => '无线蓝牙耳机 Pro',
            'sku_code' => 'SM-HP-PRO-WHT',
            'current_stock' => 45,
            'threshold' => 50,
        ],
        'is_read' => true,
        'read_at' => now(),
    ]);

    // 20. 创建请求日志
    RequestLog::create([
        'request_id' => uniqid('req_'),
        'method' => 'POST',
        'path' => '/api/v1/cross-sell/orders',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'response_status' => 201,
        'response_time' => 150,
    ]);

    // 21. 创建业务日志
    BusinessLog::create([
        'level' => 'info',
        'channel' => 'order',
        'message' => '订单创建成功',
        'context' => [
            'order_no' => $order1->order_no,
            'amount' => $order1->total,
        ],
    ]);

    // 验证所有数据都已创建
    expect(Tenant::count())->toBeGreaterThanOrEqual(1);
    expect(Warehouse::count())->toBeGreaterThanOrEqual(2);
    expect(ProductCategory::count())->toBeGreaterThanOrEqual(2);
    expect(Product::count())->toBeGreaterThanOrEqual(2);
    expect(ProductTranslation::count())->toBeGreaterThanOrEqual(2);
    expect(SKU::count())->toBeGreaterThanOrEqual(3);
    expect(SkuTranslation::count())->toBeGreaterThanOrEqual(1);
    expect(Inventory::count())->toBeGreaterThanOrEqual(3);
    expect(Customer::count())->toBeGreaterThanOrEqual(2);
    expect(Order::count())->toBeGreaterThanOrEqual(2);
    expect(OrderItem::count())->toBeGreaterThanOrEqual(2);
    expect(Wallet::count())->toBeGreaterThanOrEqual(2);
    expect(PaymentRecord::count())->toBeGreaterThanOrEqual(2);
    expect(Shipment::count())->toBeGreaterThanOrEqual(1);
    expect(ShipmentItem::count())->toBeGreaterThanOrEqual(1);
    expect(Fulfillment::count())->toBeGreaterThanOrEqual(1);
    expect(Refund::count())->toBeGreaterThanOrEqual(1);
    expect(RefundItem::count())->toBeGreaterThanOrEqual(1);
    expect(AfterSale::count())->toBeGreaterThanOrEqual(1);
    expect(AdminAuditLog::count())->toBeGreaterThanOrEqual(2);
    expect(AdminNotification::count())->toBeGreaterThanOrEqual(2);
    expect(RequestLog::count())->toBeGreaterThanOrEqual(1);
    expect(BusinessLog::count())->toBeGreaterThanOrEqual(1);
});

test('权限系统数据完整性', function () {
    // 创建管理员
    $admin = Admin::factory()->create([
        'name' => '测试管理员',
        'email' => 'test-admin@crosssell.com',
    ]);
    $admin->assignRole('super_admin');

    // 验证角色
    expect($admin->hasRole('super_admin'))->toBeTrue();

    // 验证权限
    expect($admin->can('order.view'))->toBeTrue();
    expect($admin->can('product.view'))->toBeTrue();
    expect($admin->can('role.view'))->toBeTrue();
});

test('多租户数据隔离', function () {
    // 创建两个租户
    $tenant1 = Tenant::create([
        'name' => '租户A',
        'slug' => 'tenant-a',
        'status' => 'active',
        'plan' => 'free',
    ]);

    $tenant2 = Tenant::create([
        'name' => '租户B',
        'slug' => 'tenant-b',
        'status' => 'active',
        'plan' => 'pro',
    ]);

    // 为每个租户创建商品
    $product1 = Product::create([
        'tenant_id' => $tenant1->id,
        'title' => '租户A的商品',
        'status' => 'active',
    ]);

    $product2 = Product::create([
        'tenant_id' => $tenant2->id,
        'title' => '租户B的商品',
        'status' => 'active',
    ]);

    // 验证数据隔离
    $tenant1Products = Product::where('tenant_id', $tenant1->id)->get();
    $tenant2Products = Product::where('tenant_id', $tenant2->id)->get();

    expect($tenant1Products)->toHaveCount(1);
    expect($tenant1Products->first()->title)->toBe('租户A的商品');

    expect($tenant2Products)->toHaveCount(1);
    expect($tenant2Products->first()->title)->toBe('租户B的商品');
});

test('库存多仓库管理', function () {
    $tenant = Tenant::create([
        'name' => '库存测试租户',
        'slug' => 'inventory-test',
        'status' => 'active',
        'plan' => 'free',
    ]);

    $warehouse1 = Warehouse::create([
        'tenant_id' => $tenant->id,
        'name' => '仓库1',
        'is_active' => true,
    ]);

    $warehouse2 = Warehouse::create([
        'tenant_id' => $tenant->id,
        'name' => '仓库2',
        'is_active' => true,
    ]);

    $product = Product::create([
        'tenant_id' => $tenant->id,
        'title' => '多仓库商品',
        'status' => 'active',
    ]);

    $sku = SKU::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'sku_code' => 'MULTI-WAREHOUSE-001',
        'price' => 100.00,
        'stock' => 500,
    ]);

    // 在两个仓库创建库存
    $inv1 = Inventory::create([
        'tenant_id' => $tenant->id,
        'sku_id' => $sku->id,
        'warehouse_id' => $warehouse1->id,
        'quantity' => 200,
        'reserved_quantity' => 0,
        'available_quantity' => 200,
        'min_stock' => 50,
        'max_stock' => 500,
    ]);

    $inv2 = Inventory::create([
        'tenant_id' => $tenant->id,
        'sku_id' => $sku->id,
        'warehouse_id' => $warehouse2->id,
        'quantity' => 300,
        'reserved_quantity' => 0,
        'available_quantity' => 300,
        'min_stock' => 50,
        'max_stock' => 500,
    ]);

    // 扣减仓库1的库存
    $inv1->deduct(50);

    // 验证库存独立
    expect($inv1->fresh()->quantity)->toBe(150);
    expect($inv2->fresh()->quantity)->toBe(300);

    // 验证总库存
    $totalStock = Inventory::where('sku_id', $sku->id)->sum('quantity');
    expect($totalStock)->toBe(450);
});

test('订单状态流转完整流程', function () {
    $tenant = Tenant::create([
        'name' => '状态测试租户',
        'slug' => 'status-test',
        'status' => 'active',
        'plan' => 'free',
    ]);

    // 创建待处理订单
    $order = Order::create([
        'tenant_id' => $tenant->id,
        'order_no' => 'ORD-STATUS-001',
        'platform' => 'manual',
        'status' => 'pending',
        'buyer_name' => '测试用户',
        'total' => 100.00,
        'currency' => 'USD',
    ]);

    expect($order->status->value)->toBe('pending');

    // 确认订单
    $order->confirm();
    expect($order->fresh()->status->value)->toBe('confirmed');

    // 转为处理中
    $order->update(['status' => 'processing']);
    expect($order->fresh()->status->value)->toBe('processing');

    // 发货
    $order->ship();
    expect($order->fresh()->status->value)->toBe('shipped');

    // 签收
    $order->update(['status' => 'delivered']);
    expect($order->fresh()->status->value)->toBe('delivered');

    // 完成
    $order->complete();
    expect($order->fresh()->status->value)->toBe('completed');
});
