---
name: code-standards
description: "Apply this skill for code standards checking in this Laravel + Filament project. Covers: Laravel Pint (PSR-12 + custom rules), PHPStan/Larastan (level 5), Filament resource conventions, DDD layer rules, and CI pipeline checks. Use whenever checking code quality, fixing style issues, or verifying static analysis before commit."
license: MIT
metadata:
  author: laravel-filament
---

# Code Standards — 代码规范检查

> **技术栈**: Laravel 12 + Filament 3.x + PHP 8.5 + Pest
> **工具链**: Pint → PHPStan → Pest (顺序执行)

---

## 检查流程

```
./vendor/bin/pint --test     → 格式检查
./vendor/bin/phpstan analyse  → 静态分析
./vendor/bin/pest --compact  → 测试验证
```

**CI 强制顺序**: Pint 不通过 → 不运行 PHPStan; PHPStan 不通过 → 不运行 Pest

---

## 1. Laravel Pint (格式检查)

### 快速命令

```bash
# 检查（不修改文件）— CI 模式
./vendor/bin/pint --test

# 自动修复
./vendor/bin/pint

# 检查指定文件
./vendor/bin/pint app/Services/OrderService.php

# 检查指定目录
./vendor/bin/pint app/Domains/Trade/
```

### 本项目强制规则 (.php-cs-fixer.php)

| 规则 | 说明 | 强制 |
|------|------|------|
| `@PSR12` | PSR-12 编码标准 | ✅ |
| `@PSR12:risky` | PSR-12 风险规则 | ✅ |
| `@PHP82Migration` | PHP 8.2 迁移规则 | ✅ |
| `declare_strict_types` | 强制严格类型声明 | ✅ |
| `strict_param` | 严格参数模式 | ✅ |
| `array_syntax: short` | 短数组语法 `[]` | ✅ |
| `no_unused_imports` | 移除未使用的 import | ✅ |
| `ordered_imports` | import 按字母排序 (class → function → const) | ✅ |
| `trailing_comma_in_multiline` | 多行末尾逗号 | ✅ |
| `class_attributes_separation` | 类成员之间空行 | ✅ |
| `ordered_class_elements` | 类成员排序 | ✅ |

### 类成员排序规则

```php
// 排序顺序（从上到下）：
use_trait;           // 1. Trait 导入
case;                // 2. 枚举 case
constant;            // 3. 常量
constant_public;     // 4. 公开常量
constant_protected;  // 5. 受保护常量
constant_private;    // 6. 私有常量
property_public;     // 7. 公开属性
property_protected;  // 8. 受保护属性
property_private;    // 9. 私有属性
construct;           // 10. 构造函数
destruct;            // 11. 析构函数
magic;               // 12. 魔术方法
phpunit;             // 13. PHPUnit 方法
```

### 常见 Pint 违规修复

```php
// ❌ 未使用 import
use App\Models\Order;
use App\Models\Product;  // 未使用 → Pint 会移除

// ❌ 长数组语法
$array = array('key' => 'value');  // → 改为 $array = ['key' => 'value']

// ❌ 缺少 declare_strict_types
<?php  // → 必须添加 declare(strict_types=1);

// ❌ 类成员顺序错误
class Order {
    public function __construct() {}  // 构造函数
    public int $status;               // 属性 → 应在构造函数前
}
```

---

## 2. PHPStan / Larastan (静态分析)

### 快速命令

```bash
# 分析全部
./vendor/bin/phpstan analyse

# 分析指定文件
./vendor/bin/phpstan analyse app/Services/OrderService.php

# 分析指定目录
./vendor/bin/phpstan analyse app/Domains/Trade/

# 生成 baseline（忽略已知错误）
./vendor/bin/phpstan analyse --generate-baseline

# 详细输出
./vendor/bin/phpstan analyse --no-progress -v
```

### 本项目配置 (phpstan.neon)

```yaml
parameters:
    level: 5                    # Larastan 推荐级别
    paths:
        - app
        - database
        - tests
    excludePaths:
        - vendor/
        - storage/
        - bootstrap/cache/
```

### PHPStan Level 含义

| Level | 检查内容 | 本项目 |
|-------|---------|--------|
| 0 | 基本语法、未定义变量 | |
| 1 | 类型推断、dead code | |
| 2 | 未知方法、属性 | |
| 3 | 返回类型、参数类型 | |
| 4 | 基础类型检查 | |
| **5** | **高级类型推断** | **✅ 使用** |
| 6 | union type 检查 | |
| 7 | nullable type | |
| 8 | 泛型检查 | |
| 9 | 严格模式 | |

### 常见 PHPStan 错误修复

```php
// ❌ Level 5: 可能为 null 的值
$user = User::find($id);
$user->name;  // Property on potentially null value

// ✅ 修复
$user = User::find($id);
abort_if(!$user, 404);
$user->name;

// ❌ Level 5: 未声明的返回类型
public function getOrder($id)
{
    return Order::find($id);  // 返回类型未声明
}

// ✅ 修复
public function getOrder(int $id): ?Order
{
    return Order::find($id);
}

// ❌ Level 5: 参数类型未声明
public function process($data)
{
    // $data 可能是任何类型
}

// ✅ 修复
public function process(array $data): void
{
    // 明确类型
}
```

---

## 3. Filament 资源规范检查

### CI 检查规则

CI 会扫描 `app/Infrastructure/Filament/Resources` 下所有 `*Resource.php` 文件:

```bash
# 手动运行 CI 检查
find app/Infrastructure/Filament/Resources -name '*Resource.php' -print0 | while IFS= read -r -d '' file; do
    if ! grep -q "public static function form" "$file"; then
        echo "ERROR: Missing form() in $file"
        exit 1
    fi
    if ! grep -q "public static function table" "$file"; then
        echo "ERROR: Missing table() in $file"
        exit 1
    fi
done
echo "Filament conventions passed!"
```

### Filament 资源必须实现

| 方法 | 说明 | 必须 |
|------|------|------|
| `form()` | 表单定义 | ✅ |
| `table()` | 表格定义 | ✅ |
| `getRelations()` | 关联管理器 | 可选 |
| `getPages()` | 页面路由 | ✅ |

### Filament 资源代码规范

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Filament\Resources\Trade;

use App\Domains\Trade\Models\Order;
use App\Domains\Trade\Enums\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = '交易管理';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = '订单';

    protected static ?string $modelLabel = '订单';

    protected static ?string $pluralModelLabel = '订单列表';

    // ✅ 必须实现: form()
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('订单信息')
                ->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('status')
                        ->options(OrderStatus::class)
                        ->default(OrderStatus::PENDING)
                        ->required(),
                ]),
        ]);
    }

    // ✅ 必须实现: table()
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OrderStatus::PENDING->value => 'warning',
                        OrderStatus::PAID->value => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
```

---

## 4. DDD 分层规范检查

### 检查清单

| 层 | 允许依赖 | 禁止依赖 |
|----|---------|---------|
| **Domain** | PHP 标准库、Domain 内部 | 框架类、Infrastructure、Http |
| **Infrastructure** | Domain、Laravel 框架 | Http 控制器 |
| **Http** | Domain、Infrastructure | 直接操作数据库 |

### 手动检查命令

```bash
# Domain 层不应依赖 Filament
grep -r "Filament" app/Domains/ && echo "FAIL: Domain depends on Filament"

# Domain 层不应依赖 Http
grep -r "Illuminate\\Http" app/Domains/ && echo "FAIL: Domain depends on Http"

# Service 不应直接使用 Request
grep -r "Request::" app/Services/ && echo "FAIL: Service uses Request directly"
```

---

## 5. 完整检查脚本

```bash
#!/bin/bash
# scripts/code-check.sh — 完整代码质量检查

set -e

echo "=== Step 1: Pint 格式检查 ==="
./vendor/bin/pint --test
echo "✅ Pint passed"

echo ""
echo "=== Step 2: PHPStan 静态分析 ==="
./vendor/bin/phpstan analyse --no-progress
echo "✅ PHPStan passed"

echo ""
echo "=== Step 3: Filament 资源规范 ==="
find app/Infrastructure/Filament/Resources -name '*Resource.php' -print0 | while IFS= read -r -d '' file; do
    if ! grep -q "public static function form" "$file"; then
        echo "❌ Missing form() in $file"
        exit 1
    fi
    if ! grep -q "public static function table" "$file"; then
        echo "❌ Missing table() in $file"
        exit 1
    fi
done
echo "✅ Filament conventions passed"

echo ""
echo "=== Step 4: 测试验证 ==="
./vendor/bin/pest --compact
echo "✅ Tests passed"

echo ""
echo "🎉 All code quality checks passed!"
```

---

## 6. CI 流水线 (GitHub Actions)

本项目 CI 配置在 `.github/workflows/ci.yml`:

```yaml
# 检查顺序:
1. Pint Code Style       → ./vendor/bin/pint --test
2. PHPStan Static Analysis → ./vendor/bin/phpstan analyse --no-progress
3. Run Tests              → ./vendor/bin/pest --parallel
4. Filament Conventions   → find + grep 检查
```

**触发条件**:
- push 到 `main` 或 `develop` 分支
- PR 到 `main` 分支

---

## 7. 快速修复命令

```bash
# 一键修复所有格式问题
./vendor/bin/pint

# 修复后重新检查
./vendor/bin/pint --test && ./vendor/bin/phpstan analyse --no-progress

# Docker 环境
docker compose exec app ./vendor/bin/pint
docker compose exec app ./vendor/bin/phpstan analyse --no-progress
```
