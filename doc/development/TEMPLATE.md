# 开发文档模板

> **文档编号**: DEV-{模块}-{序号}
> **创建日期**: YYYY-MM-DD
> **作者**: {姓名}
> **状态**: 进行中 | 已完成 | 已废弃
> **关联设计**: DES-{模块}-{序号}

---

## 1. 开发概述

### 1.1 开发目标
{描述本次开发的目标}

### 1.2 开发范围
{描述开发的范围和边界}

### 1.3 技术要点
- {要点1}
- {要点2}
- {要点3}

---

## 2. 开发计划

### 2.1 任务分解

| 编号 | 任务 | 负责人 | 预估工时 | 状态 |
|------|------|--------|----------|------|
| T001 | 数据库迁移 | {姓名} | 2h | 待开始 |
| T002 | Model 开发 | {姓名} | 4h | 待开始 |
| T003 | Service 开发 | {姓名} | 8h | 待开始 |
| T004 | API 开发 | {姓名} | 6h | 待开始 |
| T005 | Filament 开发 | {姓名} | 8h | 待开始 |
| T006 | 测试编写 | {姓名} | 6h | 待开始 |

### 2.2 开发顺序

```
1. Migration → 2. Model → 3. Enum → 4. DTO
    ↓
5. Repository Interface → 6. Repository Eloquent
    ↓
7. Service → 8. Controller → 9. FormRequest
    ↓
10. Filament Resource → 11. Filament Pages
    ↓
12. Unit Tests → 13. Feature Tests → 14. Architecture Tests
```

### 2.3 里程碑

| 里程碑 | 日期 | 交付物 |
|--------|------|--------|
| 数据库设计完成 | YYYY-MM-DD | Migration 文件 |
| 核心逻辑完成 | YYYY-MM-DD | Service + Tests |
| API 完成 | YYYY-MM-DD | Controller + Tests |
| Filament 完成 | YYYY-MM-DD | Resource + Pages |

---

## 3. 开发环境

### 3.1 本地环境

```bash
# 启动容器
docker compose up -d

# 运行迁移
docker compose exec app php artisan migrate

# 运行测试
docker compose exec app ./vendor/bin/pest
```

### 3.2 开发工具

| 工具 | 用途 | 命令 |
|------|------|------|
| Pint | 代码格式化 | `./vendor/bin/pint` |
| PHPStan | 静态分析 | `./vendor/bin/phpstan analyse` |
| Pest | 测试框架 | `./vendor/bin/pest` |

---

## 4. 代码实现

### 4.1 文件清单

| 文件路径 | 说明 | 状态 |
|----------|------|------|
| app/Domains/{Domain}/Models/{Entity}.php | 领域模型 | 已完成 |
| app/Domains/{Domain}/Services/{Service}.php | 业务服务 | 进行中 |
| app/Domains/{Domain}/Data/{Dto}.php | 数据对象 | 待开始 |
| app/Infrastructure/Filament/Resources/{Domain}/{Entity}Resource.php | Filament 资源 | 待开始 |

### 4.2 核心代码

#### Model 实现

```php
<?php

declare(strict_types=1);

namespace App\Domains\{Domain}\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {Entity} extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
    ];
}
```

#### Service 实现

```php
<?php

declare(strict_types=1);

namespace App\Domains\{Domain}\Services;

use App\Domains\{Domain}\Models\{Entity};
use App\Domains\{Domain}\Data\{Dto}Data;

final class {Service}Service
{
    public function create({Dto}Data $data): {Entity}
    {
        return {Entity}::create($data->toArray());
    }

    public function update({Entity} $entity, {Dto}Data $data): {Entity}
    {
        $entity->update($data->toArray());
        return $entity->fresh();
    }

    public function delete({Entity} $entity): bool
    {
        return $entity->delete();
    }
}
```

---

## 5. 测试实现

### 5.1 测试覆盖

| 测试类型 | 文件路径 | 覆盖范围 | 状态 |
|----------|----------|----------|------|
| Unit/Model | tests/Unit/Models/{Entity}Test.php | Model 属性、关系 | 已完成 |
| Unit/Service | tests/Unit/Services/{Service}Test.php | 业务逻辑 | 进行中 |
| Feature/Api | tests/Feature/Api/{Entity}ApiTest.php | API 接口 | 待开始 |
| Feature/Filament | tests/Feature/Filament/{Entity}ResourceTest.php | Filament 操作 | 待开始 |
| Architecture | tests/Unit/Architecture/{Domain}LayerTest.php | DDD 边界 | 待开始 |

### 5.2 测试用例

```php
<?php

// tests/Unit/Services/{Service}Test.php

use App\Domains\{Domain}\Services\{Service}Service;
use App\Domains\{Domain}\Data\{Dto}Data;
use App\Domains\{Domain}\Models\{Entity};

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app({Service}Service::class);
});

test('create {entity} with valid data', function () {
    $data = new {Dto}Data(
        name: 'Test',
        status: 'active',
        amount: 100.00,
    );
    
    $entity = $this->service->create($data);
    
    expect($entity)->toBeInstanceOf({Entity}::class)
        ->and($entity->name)->toBe('Test')
        ->and($entity->status)->toBe('active')
        ->and($entity->amount)->toBe('100.00');
});

test('update {entity} with valid data', function () {
    $entity = {Entity}::factory()->create();
    $data = new {Dto}Data(
        name: 'Updated',
        status: 'inactive',
        amount: 200.00,
    );
    
    $updated = $this->service->update($entity, $data);
    
    expect($updated->name)->toBe('Updated')
        ->and($updated->status)->toBe('inactive')
        ->and($updated->amount)->toBe('200.00');
});
```

---

## 6. 代码质量

### 6.1 检查清单

- [ ] Pint 格式检查通过
- [ ] PHPStan 静态分析通过
- [ ] 所有测试通过
- [ ] 架构测试通过
- [ ] 代码注释完整
- [ ] 提交信息规范

### 6.2 检查命令

```bash
# 完整检查
./vendor/bin/pint --test && \
./vendor/bin/phpstan analyse --no-progress && \
./vendor/bin/pest --compact
```

---

## 7. 问题记录

### 7.1 遇到的问题

| 问题 | 原因 | 解决方案 | 状态 |
|------|------|----------|------|
| {问题1} | {原因} | {解决方案} | 已解决 |
| {问题2} | {原因} | {解决方案} | 进行中 |

### 7.2 待优化项

| 优化项 | 优先级 | 预估工时 | 状态 |
|--------|--------|----------|------|
| {优化1} | P1 | 2h | 待开始 |
| {优化2} | P2 | 4h | 待开始 |

---

## 8. 部署说明

### 8.1 部署步骤

```bash
# 1. 拉取最新代码
git pull origin main

# 2. 安装依赖
composer install --no-dev --optimize-autoloader

# 3. 运行迁移
php artisan migrate --force

# 4. 清除缓存
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# 5. 重启队列
php artisan queue:restart
```

### 8.2 回滚方案

```bash
# 回滚迁移
php artisan migrate:rollback

# 回滚代码
git revert HEAD
```

---

## 9. 签署

| 角色 | 姓名 | 日期 | 签名 |
|------|------|------|------|
| 开发人员 | | | |
| 代码审查人员 | | | |

---

## 变更记录

| 版本 | 日期 | 作者 | 变更内容 |
|------|------|------|----------|
| 1.0 | YYYY-MM-DD | {姓名} | 初始版本 |
