---
name: database-design
description: "Apply this skill for database design in this Laravel + Filament project. Covers: migration creation, schema design following DDD conventions, naming standards, index strategy, foreign keys, soft deletes, and migration safety rules. Use whenever creating or modifying database migrations, models, or schema."
license: MIT
metadata:
  author: laravel-filament
---

# Database Design — 数据库设计规范

> **技术栈**: Laravel 12 + MySQL 8.0 + Eloquent
> **架构**: DDD 分层，Domain Model 定义表结构

---

## Migration 命令

```bash
# 创建迁移
docker compose exec app php artisan make:migration create_{table}_table

# 运行迁移
docker compose exec app php artisan migrate

# 回滚最后一批迁移
docker compose exec app php artisan migrate:rollback

# 重新迁移（危险！清空数据）
docker compose exec app php artisan migrate:fresh

# 重新迁移 + 填充
docker compose exec app php artisan migrate:fresh --seed
```

---

## 表设计规范

### 主键

```php
// ✅ 标准自增主键
$table->id();  // BIGINT UNSIGNED AUTO_INCREMENT

// ✅ UUID 主键（公开 API）
$table->uuid('id')->primary();

// ✅ ULID 主键（可排序）
$table->ulid('id')->primary();
```

### 外键

```php
// ✅ 标准外键（constrained 自动推断表名和列名）
$table->foreignId('user_id')->constrained();

// ✅ 级联删除
$table->foreignId('order_id')->constrained()->cascadeOnDelete();

// ✅ 置空删除
$table->foreignId('customer_id')->constrained()->nullOnDelete();

// ✅ 自定义表名
$table->foreignId('parent_id')
    ->constrained('addresses')
    ->cascadeOnDelete();

// ❌ 禁止：手动定义外键
$table->unsignedBigInteger('user_id');
$table->foreign('user_id')->references('id')->on('users');
```

### 软删除

```php
// ✅ 核心业务表必须开启软删除
$table->softDeletes();

// ❌ 禁止：已生产环境的 migration 中添加软删除
// 应创建新 migration
$table->softDeletes();  // 只在新表的 create migration 中
```

### 金额字段

```php
// ✅ 金额必须使用 DECIMAL
$table->decimal('amount', 10, 2);        // 最大 99,999,999.99
$table->decimal('unit_price', 10, 2);
$table->decimal('total_amount', 12, 2);  // 大金额

// ❌ 禁止：使用 FLOAT/DOUBLE
$table->float('amount');     // 精度丢失
$table->double('amount');    // 精度丢失
```

### 状态字段

```php
// ✅ 使用 string + default（推荐）
$table->string('status')->default('pending');

// ✅ 使用 enum（MySQL 原生）
$table->enum('status', ['pending', 'paid', 'shipped']);

// ✅ 使用 tinyint（性能最好）
$table->tinyInteger('status')->default(0);
```

### 时间戳

```php
// ✅ 标准时间戳
$table->timestamps();  // created_at + updated_at

// ✅ 带软删除
$table->timestamps();
$table->softDeletes();

// ✅ 自定义时间戳
$table->timestamp('paid_at')->nullable();
$table->timestamp('shipped_at')->nullable();
$table->timestamp('completed_at')->nullable();
```

### JSON 字段

```php
// ✅ 可选 JSON 字段
$table->json('metadata')->nullable();
$table->json('custom_fields')->nullable();

// ⚠️ JSON 字段索引（MySQL 5.7+）
$table->json('tags')->nullable();
// 不能直接创建索引，需用 raw SQL 或应用层过滤
```

---

## 命名规范

### 表名

| 规则 | 示例 | 说明 |
|------|------|------|
| snake_case + 复数 | `orders` | 标准 Laravel |
| 业务前缀 | `trade_orders` | 按业务域分组 |
| 关联表 | `order_items` | 中间表用蛇形复数 |

### 列名

| 规则 | 示例 | 说明 |
|------|------|------|
| snake_case | `created_at` | 标准 |
| 外键 | `user_id` | `{model}_id` |
| 布尔 | `is_active` | `is_` 前缀 |
| 时间 | `paid_at` | `{动词}_at` |
| 金额 | `unit_price` | 明确含义 |

### 索引名

```php
// 索引命名规则
$table->index('email');                          // idx_orders_email
$table->unique('order_number');                   // uniq_orders_order_number
$table->index(['user_id', 'status']);             // idx_orders_user_id_status
$table->foreignId('user_id')->constrained();     // 自动命名
```

---

## 完整 Migration 模板

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
            // 主键
            $table->id();
            
            // 外键
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->nullOnDelete();
            
            // 业务字段
            $table->string('order_number')->unique();
            $table->string('status')->default('pending');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('payable_amount', 12, 2);
            
            // 详情
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            // 时间戳
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(['customer_id', 'status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

---

## 关系模型模板

```php
<?php

namespace App\Domains\Trade\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'store_id',
        'order_number',
        'status',
        'total_amount',
        'discount_amount',
        'payable_amount',
        'notes',
        'metadata',
        'paid_at',
        'shipped_at',
        'completed_at',
    ];

    protected $hidden = [
        'metadata',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // 关系
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // 作用域
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
```

---

## 安全规则

### 已生产 Migration 修改

```
⚠️ 绝对禁止修改已运行的 production migration！

正确做法：创建新 migration
php artisan make:migration alter_orders_add_column_xxx
```

### 数据迁移

```php
// ❌ 禁止：在 migration 中插入数据
Schema::create('categories', function (Blueprint $table) {
    // ...
});
DB::table('categories')->insert(['name' => 'Electronics']);

// ✅ 正确：使用 Seeder 或单独的 data migration
class SeedCategories extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(50)->create();
    }
}
```

### 大表迁移

```php
// ⚠️ 大表（>100万行）迁移注意
// 1. 避免锁表操作
// 2. 使用 pt-online-schema-change（Percona Toolkit）
// 3. 分批处理数据

// 分批更新
DB::table('orders')
    ->whereNull('paid_at')
    ->chunkById(1000, function ($orders) {
        foreach ($orders as $order) {
            // 处理逻辑
        }
    });
```

---

## 检查清单

创建/修改 migration 时:

- [ ] 主键: `$table->id()` 或 `$table->uuid()->primary()`
- [ ] 外键: `$table->foreignId('xxx_id')->constrained()`
- [ ] 金额: `$table->decimal('xxx', 10, 2)`
- [ ] 状态: `$table->string('status')->default('pending')`
- [ ] 时间: `$table->timestamps()` + `$table->softDeletes()`
- [ ] 索引: 常用查询字段加 `$table->index()`
- [ ] 命名: snake_case，复数表名
- [ ] down(): `Schema::dropIfExists('xxx')`
