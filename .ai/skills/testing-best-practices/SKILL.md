# 🧪 Laravel 测试最佳实践 Skill

> **用途**: AI 辅助开发时的测试快速参考  
> **技术栈**: Laravel 12 + Pest + MySQL 8.0  
> **版本**: v1.0

---

## 🎯 核心原则

```
📦 单元测试 (70%) - Service/DTO/ValueObject
🔗 集成测试 (25%) - API/Database/Events
🌐 E2E 测试 (5%) - 核心用户旅程
```

---

## 🚀 快速开始

### 1. 安装 Pest

```bash
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev
php artisan pest:install
```

### 2. 配置测试环境

**.env.testing**（Git 忽略）：
```bash
APP_ENV=testing
DB_CONNECTION=mysql
DB_DATABASE=laravel_filament_test  # ⚠️ 独立测试库
BCRYPT_ROUNDS=4
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
```

### 3. 创建测试数据库

```sql
CREATE DATABASE `laravel_filament_test` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 📁 目录结构（匹配 DDD）

```
tests/
├── Unit/Domains/Trade/
│   ├── OrderServiceTest.php      # Service 层测试
│   └── OrderDTOTest.php          # DTO 验证测试
├── Feature/Api/
│   └── OrderApiTest.php          # API 集成测试
├── Feature/Filament/
│   └── AdminResourceTest.php     # Filament 测试
└── Traits/
    └── RefreshDatabaseSafe.php   # 安全刷新数据库
```

---

## 🔒 数据库安全防护

### 使用 RefreshDatabaseSafe Trait

```php
// tests/Traits/RefreshDatabaseSafe.php
trait RefreshDatabaseSafe {
    use RefreshDatabase;
    
    protected function refreshTestDatabase(): void {
        // 安全检查1：必须是 testing 环境
        if (app()->environment() !== 'testing') {
            throw new \RuntimeException('Cannot refresh in non-testing env!');
        }
        
        // 安全检查2：数据库名必须包含 'test'
        $dbName = config('database.connections.mysql.database');
        if (!str_contains($dbName, 'test')) {
            throw new \RuntimeException("Unsafe database: {$dbName}");
        }
        
        $this->artisan('migrate:fresh', ['--database' => 'mysql']);
    }
}
```

### 在测试中使用

```php
use Tests\Traits\RefreshDatabaseSafe;

uses(RefreshDatabaseSafe::class);

test('create order', function () {
    // 数据库已自动迁移并清空
    $product = Product::factory()->create();
    // ... 测试逻辑
});
```

---

## 📝 测试模板

### 单元测试（Service 层）

```php
// tests/Unit/Domains/Trade/OrderServiceTest.php

uses(RefreshDatabaseSafe::class);

beforeEach(function () {
    $this->service = app(OrderService::class);
});

test('create order should deduct stock', function () {
    // Arrange
    $product = Product::factory()->create(['stock' => 10]);
    $customer = Customer::factory()->create();
    
    $dto = new OrderCreateData(
        customerId: $customer->id,
        items: [new OrderItemData(productId: $product->id, quantity: 2)]
    );
    
    // Act
    $order = $this->service->create($dto);
    
    // Assert
    expect($order->status)->toBe(OrderStatus::PENDING);
    expect($product->refresh()->stock)->toBe(8);
});

test('insufficient stock should throw exception', function () {
    $product = Product::factory()->create(['stock' => 2]);
    $customer = Customer::factory()->create();
    
    $dto = new OrderCreateData(
        customerId: $customer->id,
        items: [new OrderItemData(productId: $product->id, quantity: 5)]
    );
    
    expect(fn () => $this->service->create($dto))
        ->toThrow(InsufficientStockException::class);
});
```

---

### 集成测试（API）

```php
// tests/Feature/Api/OrderApiTest.php

test('authenticated customer can create order', function () {
    // Arrange
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price' => 99.00, 'stock' => 10]);
    
    // Act
    $response = $this->actingAs($customer, 'customer')
        ->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'shipping_address' => '123 Test St'
        ]);
    
    // Assert
    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.total_amount', '198.00');
    
    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'status' => 'pending'
    ]);
});
```

---

### Filament 测试

```php
// tests/Feature/Filament/AdminResourceTest.php

use Livewire\Livewire;
use Filament\Actions\DeleteAction;

test('admin can list orders', function () {
    $admin = Admin::factory()->create();
    $orders = Order::factory()->count(5)->create();
    
    Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(5);
});

test('admin can delete order', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create();
    
    Livewire::test(\App\Filament\Resources\OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey()
    ])->callAction(DeleteAction::class);
    
    $this->assertSoftDeleted($order);
});
```

---

## 🏭 Factory 示例

```php
// database/factories/OrderFactory.php

public function definition(): array
{
    return [
        'customer_id' => Customer::factory(),
        'status' => OrderStatus::PENDING,
        'total_amount' => $this->faker->randomFloat(2, 10, 1000),
        'shipping_address' => $this->faker->address(),
    ];
}

// 自定义状态
public function paid(): static
{
    return $this->state(fn (array $attributes) => [
        'status' => OrderStatus::PAID,
        'paid_at' => now(),
    ]);
}

// 使用
$order = Order::factory()->paid()->create();
```

---

## 🎯 命名规范

### 单元测试
```php
test('create_order_with_sufficient_stock_should_succeed')
test('create_order_with_insufficient_stock_should_throw_exception')
test('calculate_commission_for_level1_distributor')
```

### 集成测试
```php
test('authenticated_customer_can_create_order')
test('unauthenticated_user_cannot_access_admin_panel')
test('admin_can_publish_product')
```

---

## ✅ AAA 模式

```php
test('order total amount calculation', function () {
    // Arrange（准备）
    $product1 = Product::factory()->create(['price' => 100.00]);
    $product2 = Product::factory()->create(['price' => 50.00]);
    
    $dto = new OrderCreateData([
        'items' => [
            ['product_id' => $product1->id, 'quantity' => 2],
            ['product_id' => $product2->id, 'quantity' => 1],
        ]
    ]);
    
    // Act（执行）
    $order = app(OrderService::class)->create($dto);
    
    // Assert（断言）
    expect($order->total_amount)->toBe(250.00); // (100*2) + (50*1)
});
```

---

## 🚫 常见错误

### ❌ 不要在 Migration 中插入数据

```php
// ❌ 错误
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        // ...
    });
    
    DB::table('categories')->insert(['name' => 'Electronics']); // 不要这样做！
}

// ✅ 正确：使用 Seeder
Product::factory()->count(50)->create();
```

### ❌ 不要在单元测试中访问真实数据库

```php
// ❌ 错误
test('bad example', function () {
    DB::table('users')->insert(['name' => 'Test']); // 不要直接操作 DB
});

// ✅ 正确：使用 Mock 或 Factory
$product = Product::factory()->create();
```

---

## 📊 覆盖率目标

| 层级 | 目标 | 重点 |
|------|------|------|
| Service | 80%+ | 订单、支付、库存等核心逻辑 |
| API | 70%+ | RESTful 接口 |
| Filament | 60%+ | 后台管理操作 |
| E2E | 核心路径 | 下单、支付流程 |

---

## 🛠️ 常用命令

```bash
# 运行所有测试
php artisan test

# 运行单元测试
php artisan test tests/Unit

# 运行集成测试
php artisan test tests/Feature

# 生成覆盖率报告
php artisan test --coverage-html=coverage

# 并行运行（加速）
php artisan test --parallel
```

---

## 💡 最佳实践

### ✅ Do's
- 每个 Service 方法至少 1 个单元测试
- 使用 Factory 生成测试数据
- 测试名称清晰描述场景
- 遵循 AAA 模式
- 定期运行测试（Git Hook）

### ❌ Don'ts
- 不要测试 Laravel 框架本身
- 不要在单元测试中访问真实数据库
- 不要编写依赖执行顺序的测试
- 不要忽略失败的测试

---

## 🔗 完整文档

详细指南：[02-testing-strategy.md](./02-testing-strategy.md)

---

**版本**: v1.0 | **更新**: 2026-04-27
