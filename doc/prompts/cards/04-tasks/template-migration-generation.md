# 任务模板：数据库迁移生成 (Database Migration)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
标准化数据库表结构的创建过程，确保字段类型、索引和外键设计合理。

## 适用场景
- 新功能开发初期的数据库建模
- 修改现有表结构时

## 标准内容块
```markdown
# 任务：创建 {table_name} 迁移文件

## L3: 角色设定
### DBA专家 (DBAExpert)
专注数据建模、索引策略和性能优化。

## 要求
1. **字段类型**：根据数据类型选择最合适的 MySQL 字段（金额用 `decimal(12,4)`）
2. **索引优化**：为所有查询、排序、外键字段添加索引
3. **外键约束**：显式定义 `foreignId` 并设置 `onDelete('restrict')` 防止误删
4. **注释规范**：每个字段必须包含中文注释

## 🎯 设计方案（必须解释）

### 1. 表结构设计
{描述表的核心职责}

### 2. 字段设计
| 字段名 | 类型 | 约束 | 说明 | 设计原因 |
|--------|------|------|------|---------|
| | | | | |

### 3. 索引设计
| 索引名 | 字段 | 类型 | 查询场景 |
|--------|------|------|---------|
| | | | |

### 4. 外键设计
| 关联表 | 关联字段 | 删除策略 | 设计原因 |
|--------|---------|---------|---------|
| | | | |

### 5. 性能考虑
- 预估数据量：?
- 查询热点：?
- 分表策略：?

## 💻 代码实现
```php
<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 4);
            $table->unsignedInteger('stock')->default(0);
            $table->json('specifications')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

## L5: 验收标准
- [ ] 所有字段有类型和注释
- [ ] 查询字段有索引
- [ ] 外键使用 restrictOnDelete
- [ ] 有 softDeletes
- [ ] 有合理的索引设计解释
```
```
