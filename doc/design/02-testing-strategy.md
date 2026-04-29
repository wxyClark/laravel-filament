# 🧪 软件测试方案与最佳实践

> **版本**: v1.0.0  
> **技术栈**: Laravel 12 + Pest + MySQL 8.0 + Redis 7.0  
> **适用范围**: Laravel Filament 电商系统  
> **最后更新**: 2026-04-27

---

## 📋 目录

- [1. 测试策略总览](#1-测试策略总览)
- [2. 测试金字塔设计](#2-测试金字塔设计)
- [3. 测试工具链选型](#3-测试工具链选型)
- [4. 数据库迁移管理方案](#4-数据库迁移管理方案)
- [5. 测试用例管理规范](#5-测试用例管理规范)
- [6. DDD 分层测试实践](#6-ddd-分层测试实践)
- [7. CI/CD 集成方案](#7-cicd-集成方案)
- [8. 性能与安全测试](#8-性能与安全测试)

---

## 1. 测试策略总览

### 1.1 核心原则

基于您的 **DDD 架构**和**一人公司**开发模式，我们采用以下测试策略：

```
🎯 目标：用最小的测试成本，获得最大的质量保障
⚖️ 平衡：测试覆盖率 vs 开发效率
🔄 迭代：TDD（关键模块）+ BDD（业务流程）+ E2E（核心路径）
```

### 1.2 测试覆盖优先级

| 优先级 | 测试类型 | 覆盖率目标 | 适用场景 |
|--------|---------|-----------|---------|
| **P0** | 单元测试（Service层） | 80%+ | 订单、支付、库存等核心业务逻辑 |
| **P1** | 集成测试（API） | 70%+ | RESTful API 接口 |
| **P2** | 功能测试（Filament） | 60%+ | 后台管理操作 |
| **P3** | E2E 测试 | 核心路径 | 用户下单、支付流程 |

---

## 2. 测试金字塔设计

### 2.1 金字塔结构

```
           /\
          /  \         E2E Tests (5%)
         /----\        - Playwright/Cypress
        /      \       
       /--------\      Integration Tests (25%)
      /          \     - API Tests (Pest)
     /------------\    
    /              \   Unit Tests (70%)
   /----------------\  - Service Layer
  /                  \ - DTO Validation
 /                    \- Repository Mocks
```

### 2.2 各层级职责

#### 📦 单元测试（70%）

**测试对象**：
- Service 层业务逻辑
- DTO 数据转换
- Value Object 值对象
- Policy 权限策略

**特点**：
- ⚡ 执行速度快（毫秒级）
- 🔒 不依赖数据库/外部服务
- 🎯 隔离性强，易于定位问题

**示例**：
```php
// tests/Unit/Domains/Trade/OrderServiceTest.php
test('create order should deduct stock', function () {
    // Arrange
    $product = Product::factory()->create(['stock' => 10]);
    $dto = new OrderCreateData(
        productId: $product->id,
        quantity: 2
    );
    
    // Act
    $order = app(OrderService::class)->create($dto);
    
    // Assert
    expect($order->status)->toBe(OrderStatus::PENDING);
    expect($product->refresh()->stock)->toBe(8);
});
```

---

#### 🔗 集成测试（25%）

**测试对象**：
- HTTP API 接口
- 数据库交互
- 队列任务
- 事件监听器

**特点**：
- 🗄️ 使用真实数据库（SQLite in-memory）
- 📨 测试组件间协作
- ⏱️ 执行速度中等（秒级）

**示例**：
```php
// tests/Feature/Api/OrderApiTest.php
test('customer can create order via api', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price' => 99.00]);
    
    $response = $this->actingAs($customer, 'customer')
        ->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    
    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.total_amount', '99.00');
});
```

---

#### 🌐 E2E 测试（5%）

**测试对象**：
- 完整用户旅程
- 前端交互流程
- 跨系统集成

**特点**：
- 🖥️ 模拟真实用户操作
- 🐢 执行速度慢（分钟级）
- 💰 维护成本高

**示例**：
```php
// tests/Browser/OrderFlowTest.php
test('customer can complete purchase flow', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/products/1')
            ->click('@add-to-cart')
            ->visit('/cart')
            ->click('@checkout')
            ->fill('@address', '123 Test St')
            ->click('@place-order')
            ->waitForText('Order placed successfully')
            ->assertSee('Order #12345');
    });
});
```

---

## 3. 测试工具链选型

### 3.1 核心工具栈

| 工具 | 用途 | 推荐理由 |
|------|------|---------|
| **Pest PHP** | 单元测试/集成测试 | ✅ Laravel 官方推荐，语法简洁，兼容 PHPUnit |
| **Laravel Sail** | 测试环境 | ✅ 与生产环境一致，避免环境问题 |
| **SQLite (in-memory)** | 单元测试数据库 | ⚡ 速度快，无需配置 |
| **MySQL 8.0** | 集成测试数据库 | 🎯 与生产环境一致，支持 CTE |
| **Playwright** | E2E 测试 | 🚀 比 Cypress 更快，支持多浏览器 |
| **Mockery** | Mock 框架 | 🔧 Pest 内置支持 |
| **FakerPHP** | 测试数据生成 | 📊 Laravel 内置，快速创建工厂数据 |

### 3.2 Pest 配置优化

**phpunit.xml** 关键配置：
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <!-- 环境变量 -->
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        
        <!-- 测试数据库配置 -->
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

### 3.3 Pest 插件推荐

```bash
# 安装 Pest 插件
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev

# 可选：代码覆盖率报告
composer require phpunit/php-code-coverage --dev
```

---

## 4. 数据库迁移管理方案

### ⚠️ 核心问题：如何避免测试时清空生产数据库？

### 4.1 方案对比

| 方案 | 优点 | 缺点 | 推荐度 |
|------|------|------|--------|
| **SQLite in-memory** | ⚡ 极速，完全隔离 | ❌ 不支持 MySQL 特性（CTE） | ⭐⭐⭐ |
| **独立测试数据库** | 🎯 与生产一致 | ⚠️ 需要额外配置 | ⭐⭐⭐⭐⭐ |
| **Docker 隔离容器** | 🔒 完全隔离 | 🐢 启动慢 | ⭐⭐⭐⭐ |

### 4.2 推荐方案：独立测试数据库 + Migration 管理

#### 步骤 1：创建测试数据库

```sql
-- 在 MySQL 中执行
CREATE DATABASE `laravel_filament_test` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

CREATE USER 'test_user'@'localhost' IDENTIFIED BY 'test_password';
GRANT ALL PRIVILEGES ON `laravel_filament_test`.* TO 'test_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 步骤 2：配置 .env.testing

```bash
# .env.testing（Git 忽略）
APP_ENV=testing
APP_DEBUG=true

# 测试数据库配置（与生产完全隔离）
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_filament_test
DB_USERNAME=test_user
DB_PASSWORD=test_password

# 测试优化配置
BCRYPT_ROUNDS=4
CACHE_DRIVER=array
MAIL_MAILER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
REDIS_CLIENT=phpredis
```

#### 步骤 3：创建测试 Helper Trait

```php
// tests/Traits/RefreshDatabaseSafe.php
<?php

namespace Tests\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

trait RefreshDatabaseSafe
{
    use RefreshDatabase;
    
    /**
     * 安全地刷新数据库（仅操作测试库）
     */
    protected function refreshTestDatabase(): void
    {
        // 安全检查：确保是测试环境
        if (app()->environment() !== 'testing') {
            throw new \RuntimeException(
                'Cannot refresh database in non-testing environment!'
            );
        }
        
        // 二次检查：数据库名必须包含 'test'
        $dbName = config('database.connections.mysql.database');
        if (!str_contains($dbName, 'test')) {
            throw new \RuntimeException(
                "Database name '{$dbName}' does not contain 'test'. Aborting for safety!"
            );
        }
        
        // 执行迁移
        $this->artisan('migrate:fresh', [
            '--database' => 'mysql',
            '--seed' => false, // 测试中手动 seeding
        ]);
    }
}
```

#### 步骤 4：在测试中使用

```php
// tests/Feature/OrderTest.php
use Tests\Traits\RefreshDatabaseSafe;

uses(RefreshDatabaseSafe::class);

test('create order', function () {
    // 数据库已自动迁移并清空
    $product = Product::factory()->create();
    
    // ... 测试逻辑
});
```

### 4.3 Migration 管理最佳实践

#### ✅ 正确做法

**1. Migration 只负责结构变更**
```php
// database/migrations/2024_01_01_create_orders_table.php
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->decimal('total_amount', 10, 2);
        $table->string('status')->default('pending');
        $table->timestamps();
        $table->softDeletes(); // DDD 要求
    });
}
```

**2. 数据填充使用 Seeder**
```php
// database/seeders/ProductSeeder.php
public function run(): void
{
    Product::factory()->count(50)->create();
}
```

**3. 测试中手动 Seeding**
```php
test('with seeded data', function () {
    $this->artisan('db:seed', ['--class' => 'ProductSeeder']);
    
    expect(Product::count())->toBe(50);
});
```

#### ❌ 错误做法

**不要在 Migration 中插入数据**：
```php
// ❌ 错误示例
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        // ...
    });
    
    // 不要这样做！
    DB::table('categories')->insert([
        'name' => 'Electronics'
    ]);
}
```

**原因**：
- Migration 回滚时会删除数据
- 测试环境无法灵活控制数据
- 违反单一职责原则

### 4.4 数据库安全防护机制

#### 防护 1：环境检查中间件

```php
// app/Providers/TestSafetyProvider.php
public function boot(): void
{
    // 防止在生产环境运行测试命令
    if (app()->environment('production')) {
        // 禁用危险命令
        Artisan::command('migrate:fresh', function () {
            $this->error('Cannot run migrate:fresh in production!');
        })->denyInProduction();
        
        Artisan::command('db:wipe', function () {
            $this->error('Cannot run db:wipe in production!');
        })->denyInProduction();
    }
}
```

#### 防护 2：数据库名前缀约定

```bash
# 命名规范
生产库：laravel_filament_prod
测试库：laravel_filament_test
预发库：laravel_filament_staging
```

#### 防护 3：CI/CD 中的隔离

```yaml
# .github/workflows/test.yml
jobs:
  test:
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: laravel_filament_test
        ports:
          - 3306:3306
```

---

## 5. 测试用例管理规范

### 5.1 目录结构（匹配 DDD 架构）

```
tests/
├── Unit/                      # 单元测试
│   ├── Domains/
│   │   ├── Trade/
│   │   │   ├── OrderServiceTest.php
│   │   │   ├── OrderDTOTest.php
│   │   │   └── OrderStatusTest.php
│   │   ├── Product/
│   │   │   └── ProductServiceTest.php
│   │   └── User/
│   │       └── RBACPolicyTest.php
│   └── Infrastructure/
│       └── Repositories/
│           └── OrderRepositoryTest.php
│
├── Feature/                   # 集成测试
│   ├── Api/
│   │   ├── OrderApiTest.php
│   │   └── ProductApiTest.php
│   ├── Filament/
│   │   ├── AdminResourceTest.php
│   │   └── DashboardTest.php
│   └── Events/
│       └── OrderCreatedListenerTest.php
│
├── Browser/                   # E2E 测试
│   ├── CustomerFlowTest.php
│   └── AdminFlowTest.php
│
├── Traits/                    # 测试工具
│   ├── RefreshDatabaseSafe.php
│   └── CreatesApplication.php
│
├── Factories/                 # 工厂（可复用 app/Database/Factories）
│   ├── OrderFactory.php
│   └── ProductFactory.php
│
└── Pest.php                   # Pest 全局配置
```

### 5.2 测试命名规范

#### 单元测试命名

```php
// 格式：test_{method}_{scenario}_{expected_result}

test('create_order_with_sufficient_stock_should_succeed')
test('create_order_with_insufficient_stock_should_throw_exception')
test('calculate_commission_for_level1_distributor')
test('validate_order_dto_with_negative_quantity_should_fail')
```

#### 集成测试命名

```php
// 格式：test_{user_role}_can_{action}_{resource}

test('authenticated_customer_can_create_order')
test('unauthenticated_user_cannot_access_admin_panel')
test('admin_can_publish_product')
test('finance_manager_can_refund_order')
```

### 5.3 AAA 模式（Arrange-Act-Assert）

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

### 5.4 测试数据工厂

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

## 6. DDD 分层测试实践

### 6.1 Domain 层测试（核心）

#### Service 层测试

```php
// tests/Unit/Domains/Trade/OrderServiceTest.php

uses(RefreshDatabaseSafe::class);

beforeEach(function () {
    $this->service = app(OrderService::class);
});

test('create order should trigger inventory deduction', function () {
    // Arrange
    $product = Product::factory()->create(['stock' => 10]);
    $customer = Customer::factory()->create();
    
    $dto = new OrderCreateData(
        customerId: $customer->id,
        items: [
            new OrderItemData(
                productId: $product->id,
                quantity: 3
            )
        ]
    );
    
    // Act
    $order = $this->service->create($dto);
    
    // Assert
    expect($order)->toBeInstanceOf(Order::class);
    expect($order->status)->toBe(OrderStatus::PENDING);
    expect($product->refresh()->stock)->toBe(7);
});

test('create order with insufficient stock should throw exception', function () {
    // Arrange
    $product = Product::factory()->create(['stock' => 2]);
    $customer = Customer::factory()->create();
    
    $dto = new OrderCreateData(
        customerId: $customer->id,
        items: [
            new OrderItemData(
                productId: $product->id,
                quantity: 5
            )
        ]
    );
    
    // Act & Assert
    expect(fn () => $this->service->create($dto))
        ->toThrow(InsufficientStockException::class);
});
```

#### DTO 验证测试

```php
// tests/Unit/Domains/Trade/Data/OrderCreateDataTest.php

test('order create dto validation', function () {
    // Valid data
    $dto = new OrderCreateData(
        customerId: 1,
        items: [
            new OrderItemData(productId: 1, quantity: 2)
        ]
    );
    
    expect($dto->customerId)->toBe(1);
    expect($dto->items)->toHaveCount(1);
});

test('order create dto with negative quantity should fail', function () {
    expect(fn () => new OrderItemData(
        productId: 1,
        quantity: -1
    ))->toThrow(ValidationException::class);
});
```

---

### 6.2 Infrastructure 层测试

#### Repository 测试

```php
// tests/Unit/Infrastructure/Repositories/OrderRepositoryTest.php

uses(RefreshDatabaseSafe::class);

test('repository can find pending orders', function () {
    // Arrange
    $pendingOrder = Order::factory()->pending()->create();
    $paidOrder = Order::factory()->paid()->create();
    
    $repo = app(OrderRepositoryInterface::class);
    
    // Act
    $results = $repo->findByStatus(OrderStatus::PENDING);
    
    // Assert
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($pendingOrder->id);
});
```

---

### 6.3 Application 层测试

#### API 集成测试

```php
// tests/Feature/Api/OrderApiTest.php

test('POST /api/orders creates order successfully', function () {
    // Arrange
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price' => 99.00, 'stock' => 10]);
    
    // Act
    $response = $this->actingAs($customer, 'customer')
        ->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ],
            'shipping_address' => '123 Test St'
        ]);
    
    // Assert
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'total_amount',
                'created_at'
            ]
        ])
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.total_amount', '198.00');
    
    // 数据库断言
    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'status' => 'pending'
    ]);
});
```

---

### 6.4 Presentation 层测试

#### Filament Resource 测试

```php
// tests/Feature/Filament/AdminResourceTest.php

use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('admin can list orders in filament', function () {
    // Arrange
    $admin = Admin::factory()->create();
    $orders = Order::factory()->count(5)->create();
    
    // Act & Assert
    Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(5);
});

test('admin can delete order via filament', function () {
    // Arrange
    $admin = Admin::factory()->create();
    $order = Order::factory()->create();
    
    // Act
    Livewire::test(\App\Filament\Resources\OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey()
    ])
        ->callAction(DeleteAction::class);
    
    // Assert
    $this->assertSoftDeleted($order);
});
```

---

## 7. CI/CD 集成方案

### 7.1 GitHub Actions 工作流

```yaml
# .github/workflows/test.yml
name: Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: laravel_filament_test
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
      
      redis:
        image: redis:7-alpine
        ports:
          - 6379:6379
        options: >-
          --health-cmd="redis-cli ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathuri/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, dom, fileinfo, mysql, redis
          coverage: xdebug
      
      - name: Copy .env
        run: cp .env.testing .env
      
      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist
      
      - name: Generate Key
        run: php artisan key:generate
      
      - name: Run Migrations
        run: php artisan migrate --database=mysql
      
      - name: Run Tests
        run: php artisan test --coverage
      
      - name: Upload Coverage
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage.xml
```

### 7.2 本地测试脚本

```bash
#!/bin/bash
# scripts/run-tests.sh

echo "🧪 Running Laravel Tests..."

# 1. 清理缓存
php artisan config:clear
php artisan cache:clear

# 2. 运行单元测试（快速反馈）
echo "📦 Unit Tests..."
php artisan test tests/Unit --parallel

# 3. 运行集成测试
echo "🔗 Feature Tests..."
php artisan test tests/Feature

# 4. 生成覆盖率报告（可选）
if [ "$1" == "--coverage" ]; then
    echo "📊 Generating Coverage Report..."
    php artisan test --coverage-html=coverage
    open coverage/index.html
fi

echo "✅ All tests passed!"
```

---

## 8. 性能与安全测试

### 8.1 性能测试（关键接口）

#### 使用 Laravel Telescope

```php
// 在测试中启用 Telescope
config(['telescope.enabled' => true]);

// 监控慢查询
DB::enableQueryLog();

// 执行测试
$response = $this->get('/api/products');

// 分析查询
$queries = DB::getQueryLog();
$slowQueries = collect($queries)->filter(fn ($q) => $q['time'] > 100);

expect($slowQueries)->toHaveCount(0, 'No slow queries allowed');
```

#### 负载测试（Artillery）

```yaml
# load-test.yml
config:
  target: "http://localhost:8082"
  phases:
    - duration: 60
      arrivalRate: 10  # 每秒 10 个请求

scenarios:
  - flow:
      - get:
          url: "/api/products"
      - post:
          url: "/api/orders"
          json:
            product_id: 1
            quantity: 1
```

```bash
# 执行负载测试
artillery run load-test.yml
```

---

### 8.2 安全测试

#### SQL 注入测试

```php
test('api is safe from sql injection', function () {
    $maliciousInput = "' OR '1'='1";
    
    $response = $this->postJson('/api/products/search', [
        'keyword' => $maliciousInput
    ]);
    
    // 应该返回空结果或验证错误，而不是所有数据
    expect($response->json('data'))->toHaveCount(0);
});
```

#### XSS 防护测试

```php
test('filament admin sanitizes user input', function () {
    $admin = Admin::factory()->create();
    $xssPayload = '<script>alert("XSS")</script>';
    
    Livewire::test(\App\Filament\Resources\ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey()
    ])
        ->fillForm([
            'name' => $xssPayload
        ])
        ->call('save');
    
    // 验证输出已被转义
    $product->refresh();
    expect($product->name)->not->toContain('<script>');
});
```

---

## 9. 测试最佳实践总结

### ✅ Do's

1. **每个 Service 方法至少有 1 个单元测试**
2. **使用 Factory 生成测试数据，避免硬编码**
3. **测试名称要清晰描述场景和预期结果**
4. **遵循 AAA 模式（Arrange-Act-Assert）**
5. **定期运行测试，最好在 Git Hook 中集成**
6. **测试失败时要能快速定位问题**

### ❌ Don'ts

1. **不要测试 Laravel 框架本身的功能**
2. **不要在单元测试中访问真实数据库**
3. **不要编写依赖于执行顺序的测试**
4. **不要在测试中使用 sleep()，使用 Mock 时间**
5. **不要忽略失败的测试，立即修复**
6. **不要在测试中写复杂的逻辑（保持简单）**

---

## 10. 快速开始清单

### 🚀 第一天：基础设置

- [ ] 安装 Pest：`composer require pestphp/pest --dev`
- [ ] 创建 `.env.testing` 文件
- [ ] 创建测试数据库：`laravel_filament_test`
- [ ] 配置 `phpunit.xml`
- [ ] 编写第一个单元测试

### 📅 第一周：核心模块测试

- [ ] 为 OrderService 编写单元测试
- [ ] 为 Product API 编写集成测试
- [ ] 创建 Factory 文件（Order, Product, Customer）
- [ ] 设置 CI/CD 自动化测试

### 📆 第一月：全面覆盖

- [ ] 完成所有 Service 层单元测试（80% 覆盖率）
- [ ] 完成所有 API 集成测试（70% 覆盖率）
- [ ] 编写 3-5 个核心流程的 E2E 测试
- [ ] 建立测试审查机制

---

## 📚 相关资源

- [Pest 官方文档](https://pestphp.com/docs)
- [Laravel Testing 文档](https://laravel.com/docs/testing)
- [DDD 测试策略](https://martinfowler.com/articles/domain-driven-design-testing.html)
- [测试金字塔](https://martinfowler.com/bliki/TestPyramid.html)

---

**维护者**: Laravel Filament Team  
**最后更新**: 2026-04-27  
**许可证**: MIT
