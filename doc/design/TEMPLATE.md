# 设计文档模板

> **文档编号**: DES-{模块}-{序号}
> **创建日期**: YYYY-MM-DD
> **作者**: {姓名}
> **状态**: 草稿 | 评审中 | 已确认 | 已废弃
> **关联需求**: REQ-{模块}-{序号}

---

## 1. 设计概述

### 1.1 设计目标
{描述设计要达成的目标}

### 1.2 设计原则
- {原则1}
- {原则2}
- {原则3}

### 1.3 技术选型
| 组件 | 选型 | 理由 |
|------|------|------|
| 框架 | Laravel 12 + Filament 3.x | {理由} |
| 数据库 | MySQL 8.0 | {理由} |
| 缓存 | Redis 7.0 | {理由} |
| 测试 | Pest | {理由} |

---

## 2. 架构设计

### 2.1 整体架构
```
┌─────────────────────────────────────────────────┐
│                  Presentation Layer              │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────┐│
│  │  Filament   │  │    API      │  │   Web    ││
│  │  Resources  │  │ Controllers │  │  Views   ││
│  └─────────────┘  └─────────────┘  └──────────┘│
├─────────────────────────────────────────────────┤
│                 Application Layer                │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────┐│
│  │  Services   │  │   Events    │  │   Jobs   ││
│  └─────────────┘  └─────────────┘  └──────────┘│
├─────────────────────────────────────────────────┤
│                  Domain Layer                    │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────┐│
│  │   Models    │  │   Enums     │  │   DTOs   ││
│  └─────────────┘  └─────────────┘  └──────────┘│
├─────────────────────────────────────────────────┤
│               Infrastructure Layer              │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────┐│
│  │ Repositories│  │  External   │  │  Cache   ││
│  └─────────────┘  └─────────────┘  └──────────┘│
└─────────────────────────────────────────────────┘
```

### 2.2 DDD 分层规则

| 层 | 职责 | 允许依赖 | 禁止依赖 |
|----|------|----------|----------|
| Domain | 业务逻辑 | PHP 标准库 | 框架、Infrastructure、Http |
| Infrastructure | 框架集成 | Domain | Http |
| Http | 接口层 | Domain、Infrastructure | 直接操作数据库 |
| Application | 业务编排 | Domain | Infrastructure 实现细节 |

### 2.3 模块划分
```
app/
├── Domains/
│   ├── User/          # 用户管理
│   ├── Product/       # 商品管理
│   ├── Trade/         # 交易管理
│   └── O2O/           # O2O 业务
├── Infrastructure/
│   ├── Filament/      # Filament 资源
│   └── Repositories/  # 仓储实现
└── Http/
    ├── Controllers/   # 控制器
    └── Requests/      # 表单请求
```

---

## 3. 数据库设计

### 3.1 ER 图
```
[User] 1:N [Order]
[Order] 1:N [OrderItem]
[Product] 1:N [OrderItem]
```

### 3.2 表结构

#### {表名}

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| name | varchar(255) | NOT NULL | 名称 |
| status | enum | NOT NULL, DEFAULT 'active' | 状态 |
| amount | decimal(10,2) | NOT NULL | 金额 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |
| deleted_at | timestamp | NULLABLE | 软删除时间 |

**索引**:
- `idx_status` (status)
- `idx_created_at` (created_at)

**外键**:
- `user_id` → `users.id`

### 3.3 Migration 代码

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number', 50)->unique();
            $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled']);
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

---

## 4. 接口设计

### 4.1 API 接口列表

| 方法 | 路径 | 描述 | 认证 | 权限 |
|------|------|------|------|------|
| GET | /api/orders | 获取订单列表 | 是 | order:list |
| POST | /api/orders | 创建订单 | 是 | order:create |
| GET | /api/orders/{id} | 获取订单详情 | 是 | order:read |
| PUT | /api/orders/{id} | 更新订单 | 是 | order:update |
| DELETE | /api/orders/{id} | 删除订单 | 是 | order:delete |

### 4.2 数据格式

#### 请求格式
```json
{
  "user_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ],
  "notes": "请尽快发货"
}
```

#### 响应格式
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "order_number": "ORD-20260718-001",
    "status": "pending",
    "total_amount": "199.00",
    "created_at": "2026-07-18T10:00:00Z"
  }
}
```

### 4.3 错误码

| 错误码 | 描述 | 处理方式 |
|--------|------|----------|
| 400 | 请求参数错误 | 检查请求参数 |
| 401 | 未认证 | 重新登录 |
| 403 | 无权限 | 联系管理员 |
| 404 | 资源不存在 | 检查资源 ID |
| 422 | 数据验证失败 | 检查数据格式 |
| 500 | 服务器错误 | 联系开发人员 |

---

## 5. Filament 资源设计

### 5.1 资源结构

```
app/Infrastructure/Filament/Resources/{Domain}/
├── {Entity}Resource.php        # 资源定义
├── Pages/
│   ├── List{Entity}s.php       # 列表页
│   ├── Create{Entity}.php      # 创建页
│   └── Edit{Entity}.php        # 编辑页
└── Widgets/
    └── {Entity}Stats.php       # 统计组件
```

### 5.2 表单设计

```php
public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Section::make('基本信息')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options(StatusEnum::class)
                    ->required(),
            ]),
    ]);
}
```

### 5.3 表格设计

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'inactive' => 'danger',
                    default => 'gray',
                }),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
}
```

---

## 6. 测试设计

### 6.1 测试策略

| 测试类型 | 覆盖范围 | 工具 |
|----------|----------|------|
| 单元测试 | Service、Model、DTO | Pest |
| 集成测试 | API、Filament | Pest + Livewire |
| 架构测试 | DDD 边界、命名约定 | Pest arch() |

### 6.2 测试用例

#### 单元测试
```php
test('order can be created with valid data', function () {
    $order = Order::factory()->create();
    expect($order->status)->toBe('pending');
});
```

#### 集成测试
```php
test('authenticated user can list orders', function () {
    $user = User::factory()->create();
    $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);
    
    $this->actingAs($user)
        ->getJson('/api/orders')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});
```

#### 架构测试
```php
arch('Domain layer has no framework dependencies')
    ->expect('App\Domains')
    ->not->toDependOn([
        'Filament',
        'App\Http',
        'App\Infrastructure',
    ]);
```

---

## 7. 安全设计

### 7.1 认证机制
- Laravel Sanctum (API Token)
- Session-based (Web)

### 7.2 授权机制
- Spatie Laravel-Permission
- RBAC (Role-Based Access Control)

### 7.3 数据安全
- 输入验证: FormRequest
- SQL 注入防护: Eloquent ORM
- XSS 防护: Blade 模板
- CSRF 防护: Laravel Token

---

## 8. 性能设计

### 8.1 缓存策略
| 数据 | 缓存方式 | 过期时间 |
|------|----------|----------|
| 用户信息 | Redis | 1 hour |
| 配置信息 | Config Cache | Forever |
| 查询结果 | Query Cache | 5 min |

### 8.2 优化措施
- Eager Loading 防止 N+1
- 分页查询
- 索引优化
- 队列处理耗时操作

---

## 9. 部署设计

### 9.1 环境配置
| 环境 | 用途 | 配置 |
|------|------|------|
| Local | 本地开发 | docker-compose.yml |
| Staging | 测试环境 | docker-compose.staging.yml |
| Production | 生产环境 | docker-compose.prod.yml |

### 9.2 部署流程
1. 代码推送
2. CI 检查 (Pint + PHPStan + Pest)
3. 构建镜像
4. 运行迁移
5. 部署服务

---

## 10. 签署

| 角色 | 姓名 | 日期 | 签名 |
|------|------|------|------|
| 架构师 | | | |
| 开发负责人 | | | |
| 测试负责人 | | | |

---

## 变更记录

| 版本 | 日期 | 作者 | 变更内容 |
|------|------|------|----------|
| 1.0 | YYYY-MM-DD | {姓名} | 初始版本 |
