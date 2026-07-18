---
name: tdd-workflow
description: "Apply this skill for the complete AI-assisted TDD workflow in this Laravel + Filament project. Covers: requirement analysis → architecture design → database design → Pest test writing → implementation → Filament UI → API联调 → testing verification. Use whenever starting a new feature module, user story, or business domain development."
license: MIT
metadata:
  author: laravel-filament
---

# TDD Workflow — AI 辅助测试驱动开发

> **技术栈**: Laravel 12 + Filament 3.x + Pest + PHP 8.5 + MySQL 8.0 + Redis 7.0
> **架构**: DDD 分层 (Domain / Infrastructure / Http)
> **PRD 文档**: `doc/PRD/` (RAG 友好格式，金字塔结构)

---

## 核心原则

```
🔴 Red    — 先写失败的测试（定义"做什么"）
🟢 Green  — 写最少的代码让测试通过（只做"怎么做"）
🔵 Refactor — 重构代码保持测试通过（优化"做得好"）
```

---

## 完整开发流程（7 步）

### Step 1: 需求分析 → PRD 文档化

**目标**: 将用户故事转化为可执行的需求碎片

1. 读取对应子系统的 PRD 文档: `doc/PRD/{模块}/`
2. 提取用户故事 (`stories/01-user-stories.md`)
3. 识别领域模型 (`models/domain-models.md`)
4. 确认 API 契约 (`apis/api-contracts.md`)
5. 如有状态机，确认状态流转 (`states/state-machines.md`)
6. 如有领域事件，确认事件定义 (`events/domain-events.md`)

**输出**: 需求确认清单（在对话中列出）

### Step 2: 架构设计 → 分层规划

**目标**: 确定每个文件的职责和位置

按 DDD 分层规划:

```
Domain/{模块}/
├── Models/{Entity}.php        # 领域模型（Eloquent）
├── Enums/{StatusEnum}.php     # 状态枚举
├── Services/{Service}.php     # 领域服务（业务逻辑）
├── Data/{Dto}.php             # DTO 值对象
├── Events/{Event}.php         # 领域事件
├── Repositories/{Interface}.php  # 仓储接口
└── Policies/{Policy}.php      # 权限策略

Infrastructure/
├── Filament/Resources/{模块}/{Entity}Resource.php  # Filament 资源
├── Filament/Resources/{模块}/Pages/                # CRUD 页面
├── Repositories/Eloquent/{Repository}.php          # 仓储实现
└── Filament/Widgets/{Widget}.php                   # 小组件

Http/
├── Controllers/Api/{Entity}Controller.php          # API 控制器
├── Requests/{Entity}/{StoreRequest,UpdateRequest}.php  # 表单验证
└── Resources/{Entity}Resource.php                  # API Resource
```

### Step 3: 数据库设计 → Migration

**目标**: 创建数据库迁移文件

```bash
# 在容器内执行
docker compose exec app php artisan make:migration create_{table}_table
```

**规范**:
- 主键: `$table->id()`
- 外键: `$table->foreignId('xxx_id')->constrained()->cascadeOnDelete()`
- 软删除: `$table->softDeletes()`
- 金额: `$table->decimal('amount', 10, 2)` (严禁 FLOAT/DOUBLE)
- 索引: 常用查询字段加索引
- 时间戳: `$table->timestamps()`

```bash
docker compose exec app php artisan migrate
```

### Step 4: 先写测试 → Red Phase

**目标**: 在实现之前，先用 Pest 定义"成功"的样子

```bash
# 创建测试文件
docker compose exec app ./vendor/bin/pest --init  # 如果还没初始化
```

**测试顺序**:

```
1. Unit/Models/{Entity}Test.php        — 模型属性、关系、Scope
2. Unit/Services/{Service}Test.php     — 业务逻辑（核心）
3. Feature/Api/{Entity}ApiTest.php     — API 接口
4. Feature/Filament/{Entity}Test.php   — Filament 操作
```

**Pest 测试模板**:

```php
<?php

// tests/Unit/Models/OrderTest.php

use App\Domains\Trade\Models\Order;
use App\Domains\Trade\Enums\OrderStatus;

test('order has correct fillable fields', function () {
    $order = new Order();
    expect($order->getFillable())->toContain('status', 'total_amount');
});

test('order defaults to pending status', function () {
    $order = Order::factory()->create();
    expect($order->status)->toBe(OrderStatus::PENDING);
});

test('order can scope pending orders', function () {
    Order::factory()->count(3)->pending()->create();
    Order::factory()->count(2)->paid()->create();
    
    expect(Order::pending()->count())->toBe(3);
});
```

```php
<?php

// tests/Unit/Services/OrderServiceTest.php

use App\Domains\Trade\Services\OrderService;
use App\Domains\Trade\Data\OrderCreateData;
use App\Domains\Trade\Models\Order;
use App\Domains\Product\Models\Product;
use App\Domains\User\Models\Customer;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(OrderService::class);
});

test('create order deducts stock', function () {
    // Arrange
    $product = Product::factory()->create(['stock' => 10]);
    $customer = Customer::factory()->create();
    
    // Act
    $order = $this->service->create(new OrderCreateData(
        customerId: $customer->id,
        items: [new OrderItemData(productId: $product->id, quantity: 2)]
    ));
    
    // Assert
    expect($order->status)->toBe(OrderStatus::PENDING);
    expect($product->refresh()->stock)->toBe(8);
});

test('insufficient stock throws exception', function () {
    $product = Product::factory()->create(['stock' => 2]);
    $customer = Customer::factory()->create();
    
    expect(fn () => $this->service->create(new OrderCreateData(
        customerId: $customer->id,
        items: [new OrderItemData(productId: $product->id, quantity: 5)]
    )))->toThrow(InsufficientStockException::class);
});
```

```php
<?php

// tests/Feature/Api/OrderApiTest.php

test('authenticated customer can list orders', function () {
    $customer = Customer::factory()->create();
    $orders = Order::factory()->count(3)->create(['customer_id' => $customer->id]);
    
    $this->actingAs($customer, 'customer')
        ->getJson('/api/orders')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('unauthenticated user cannot access orders', function () {
    $this->getJson('/api/orders')
        ->assertForbidden();
});
```

**运行测试确认 Red**:

```bash
docker compose exec app ./vendor/bin/pest --filter="order" --compact
```

### Step 5: 实现代码 → Green Phase

**目标**: 写最少的代码让测试通过

**实现顺序**:

```
1. Migration → 迁移文件
2. Model → 领域模型
3. Enum → 状态枚举
4. DTO → 值对象
5. Repository Interface → 仓储接口
6. Repository Eloquent → 仓储实现
7. Service → 领域服务（业务逻辑核心）
8. Controller → API 控制器
9. FormRequest → 表单验证
10. API Resource → JSON 资源
11. Filament Resource → 后台资源
12. Filament Pages → CRUD 页面
13. Filament Widgets → 小组件
```

**运行测试确认 Green**:

```bash
docker compose exec app ./vendor/bin/pest --compact
```

### Step 6: 重构优化 → Refactor Phase

**目标**: 提升代码质量，保持测试通过

**重构检查清单**:
- [ ] Pint 格式化: `docker compose exec app ./vendor/bin/pint`
- [ ] PHPStan 静态分析: `docker compose exec app ./vendor/bin/phpstan analyse`
- [ ] 测试全部通过
- [ ] 无重复代码
- [ ] 方法不超过 20 行
- [ ] 类不超过 200 行
- [ ] 有意义的变量/方法命名

### Step 7: 架构测试 → Architecture Phase

**目标**: 确保代码符合 DDD 分层规则和命名约定

```php
// tests/Unit/Architecture/{Domain}LayerTest.php

arch('{Domain} layer has no framework dependencies')
    ->expect('App\Domains\{Domain}')
    ->not->toDependOn([
        'Filament',
        'App\Http',
        'App\Infrastructure',
    ]);

arch('{Domain} services are final')
    ->expect('App\Domains\{Domain}\Services')
    ->toBeFinal();

arch('{Domain} DTOs are readonly')
    ->expect('App\Domains\{Domain}\Data')
    ->toBeReadOnly();
```

**运行架构测试**:
```bash
docker compose exec app ./vendor/bin/pest tests/Unit/Architecture/ --compact
```

### Step 8: 联调验证

**目标**: 端到端验证功能

```bash
# 1. 运行全部测试
docker compose exec app ./vendor/bin/pest --compact

# 2. 代码质量检查
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/phpstan analyse

# 3. 手动验证 Filament 页面
# 访问 http://localhost:8082/admin
# 验证 CRUD 操作

# 4. API 联调（可用 Postman 或 curl）
curl -X GET http://localhost:8082/api/xxx \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

---

## 关键命令速查

```bash
# Docker 前缀
dc="docker compose exec app"

# 测试
$dc ./vendor/bin/pest                          # 全部测试
$dc ./vendor/bin/pest --filter="testName"      # 按名称
$dc ./vendor/bin/pest tests/Unit/              # 按目录
$dc ./vendor/bin/pest --parallel               # 并行

# 代码质量
$dc ./vendor/bin/pint                          # 格式化
$dc ./vendor/bin/pint --test                   # 检查（不修改）
$dc ./vendor/bin/phpstan analyse               # 静态分析

# 生成代码
$dc php artisan make:model {Name} -mrf         # Model + Migration + Factory + Repository
$dc php artisan make:filament-resource {Name}   # Filament 资源
$dc php artisan make:test {Name} --pest        # 测试文件
$dc php artisan make:request Store{Name}        # FormRequest
$dc php artisan make:resource {Name}Resource    # API Resource

# 数据库
$dc php artisan migrate                        # 运行迁移
$dc php artisan migrate:fresh --seed           # 重新迁移+填充
```

---

## 测试命名规范

| 测试类型 | 命名模式 | 示例 |
|---------|---------|------|
| Unit/Model | `test('{entity} {behavior}')` | `test('order has correct casts')` |
| Unit/Service | `test('{action} {expected result}')` | `test('create order deducts stock')` |
| Feature/Api | `test('{role} can {action}')` | `test('admin can list orders')` |
| Feature/Filament | `test('{role} can {operation}')` | `test('admin can create order')` |

---

## 工厂 (Factory) 规范

```php
<?php

// database/factories/OrderFactory.php

namespace Database\Factories;

use App\Domains\Trade\Models\Order;
use App\Domains\Trade\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'status' => OrderStatus::PENDING,
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'shipping_address' => $this->faker->address(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::SHIPPED,
            'shipped_at' => now(),
        ]);
    }
}
```

---

## Filament 测试模板

```php
<?php

// tests/Feature/Filament/OrderResourceTest.php

use App\Domains\Trade\Models\Order;
use App\Domains\User\Models\Admin;
use Livewire\Livewire;
use Filament\Actions\DeleteAction;

test('admin can list orders', function () {
    $admin = Admin::factory()->create();
    $orders = Order::factory()->count(5)->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\Trade\OrderResource\Pages\ListOrders::class)
        ->loginAs($admin)
        ->assertCanSeeTableRecords($orders);
});

test('admin can create order', function () {
    $admin = Admin::factory()->create();
    $customer = Customer::factory()->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\Trade\OrderResource\Pages\CreateOrder::class)
        ->loginAs($admin)
        ->fillForm([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-001',
            'status' => 'pending',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('orders', ['order_number' => 'ORD-001']);
});

test('admin can delete order', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\Trade\OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->loginAs($admin)
        ->callAction(DeleteAction::class);
    
    $this->assertSoftDeleted('orders', ['id' => $order->id]);
});
```

---

## 验收标准

每个功能模块完成时，必须通过:

```bash
# 全部测试通过
$dc ./vendor/bin/pest --compact

# 代码风格检查
$dc ./vendor/bin/pint --test

# 静态分析通过
$dc ./vendor/bin/phpstan analyse --no-progress
```

**零容忍**: 测试失败、Pint 不通过、PHPStan 报错 → 不允许合并。
