# 测试文档模板

> **文档编号**: TES-{模块}-{序号}
> **创建日期**: YYYY-MM-DD
> **作者**: {姓名}
> **状态**: 草稿 | 评审中 | 已确认 | 已废弃
> **关联设计**: DES-{模块}-{序号}
> **关联开发**: DEV-{模块}-{序号}

---

## 1. 测试概述

### 1.1 测试目标
{描述测试要达成的目标}

### 1.2 测试范围
{描述测试的范围和边界}

### 1.3 测试策略
| 测试类型 | 覆盖范围 | 工具 | 执行频率 |
|----------|----------|------|----------|
| 单元测试 | Service、Model、DTO | Pest | 每次提交 |
| 集成测试 | API、Filament | Pest + Livewire | 每次提交 |
| 架构测试 | DDD 边界、命名约定 | Pest arch() | 每次提交 |
| E2E 测试 | 完整业务流程 | Pest | 每日构建 |

---

## 2. 测试环境

### 2.1 环境配置

```php
// tests/Pest.php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Feature', 'Unit');
```

### 2.2 测试数据

```php
// database/factories/{Entity}Factory.php

namespace Database\Factories;

use App\Domains\{Domain}\Models\{Entity};
use Illuminate\Database\Eloquent\Factories\Factory;

class {Entity}Factory extends Factory
{
    protected $model = {Entity}::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'status' => 'active',
            'amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
```

---

## 3. 测试用例

### 3.1 单元测试

#### 3.1.1 Model 测试

```php
<?php

// tests/Unit/Models/{Entity}Test.php

use App\Domains\{Domain}\Models\{Entity};
use App\Domains\{Domain}\Enums\{StatusEnum};

test('{entity} has correct fillable fields', function () {
    $entity = new {Entity}();
    expect($entity->getFillable())->toContain('name', 'status', 'amount');
});

test('{entity} defaults to active status', function () {
    $entity = {Entity}::factory()->create();
    expect($entity->status)->toBe('active');
});

test('{entity} can scope active records', function () {
    {Entity}::factory()->count(3)->create(['status' => 'active']);
    {Entity}::factory()->count(2)->create(['status' => 'inactive']);
    
    expect({Entity}::where('status', 'active')->count())->toBe(3);
});
```

#### 3.1.2 Service 测试

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

test('create {entity} with invalid data throws exception', function () {
    $data = new {Dto}Data(
        name: '',
        status: 'invalid',
        amount: -1,
    );
    
    expect(fn () => $this->service->create($data))
        ->toThrow(ValidationException::class);
});
```

### 3.2 集成测试

#### 3.2.1 API 测试

```php
<?php

// tests/Feature/Api/{Entity}ApiTest.php

use App\Domains\{Domain}\Models\{Entity};
use App\Domains\User\Models\User;

test('authenticated user can list {entities}', function () {
    $user = User::factory()->create();
    $entities = {Entity}::factory()->count(3)->create();
    
    $this->actingAs($user, 'sanctum')
        ->getJson('/api/{entities}')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('unauthenticated user cannot access {entities}', function () {
    $this->getJson('/api/{entities}')
        ->assertForbidden();
});

test('authenticated user can create {entity}', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/{entities}', [
            'name' => 'Test',
            'status' => 'active',
            'amount' => 100.00,
        ])
        ->assertSuccessful();
    
    $this->assertDatabaseHas('{entities}', [
        'name' => 'Test',
        'status' => 'active',
    ]);
});
```

#### 3.2.2 Filament 测试

```php
<?php

// tests/Feature/Filament/{Entity}ResourceTest.php

use App\Domains\{Domain}\Models\{Entity};
use App\Domains\User\Models\Admin;
use Livewire\Livewire;

test('admin can list {entities}', function () {
    $admin = Admin::factory()->create();
    $entities = {Entity}::factory()->count(5)->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\{Domain}\{Entity}Resource\Pages\List{Entity}s::class)
        ->loginAs($admin)
        ->assertCanSeeTableRecords($entities);
});

test('admin can create {entity}', function () {
    $admin = Admin::factory()->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\{Domain}\{Entity}Resource\Pages\Create{Entity}::class)
        ->loginAs($admin)
        ->fillForm([
            'name' => 'Test',
            'status' => 'active',
            'amount' => 100.00,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('{entities}', [
        'name' => 'Test',
        'status' => 'active',
    ]);
});
```

### 3.3 架构测试

```php
<?php

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

arch('{Domain} models use SoftDeletes')
    ->expect('App\Domains\{Domain}\Models')
    ->toUse('Illuminate\Database\Eloquent\SoftDeletes');
```

---

## 4. 测试执行

### 4.1 执行命令

```bash
# 运行所有测试
./vendor/bin/pest

# 运行指定目录测试
./vendor/bin/pest tests/Unit/

# 运行指定文件测试
./vendor/bin/pest tests/Unit/Models/{Entity}Test.php

# 运行指定测试
./vendor/bin/pest --filter="test_name"

# 运行架构测试
./vendor/bin/pest tests/Unit/Architecture/

# 并行运行测试
./vendor/bin/pest --parallel

# 显示详细输出
./vendor/bin/pest -v
```

### 4.2 执行结果

| 测试类型 | 用例数 | 通过数 | 失败数 | 耗时 |
|----------|--------|--------|--------|------|
| 单元测试 | 25 | 25 | 0 | 1.2s |
| 集成测试 | 15 | 15 | 0 | 3.5s |
| 架构测试 | 8 | 8 | 0 | 0.3s |
| **总计** | **48** | **48** | **0** | **5.0s** |

---

## 5. 测试覆盖

### 5.1 覆盖率报告

| 模块 | 文件数 | 行覆盖率 | 分支覆盖率 |
|------|--------|----------|------------|
| Models | 5 | 95% | 90% |
| Services | 8 | 88% | 82% |
| Controllers | 3 | 85% | 78% |
| **总计** | **16** | **90%** | **85%** |

### 5.2 未覆盖项

| 未覆盖项 | 原因 | 计划 |
|----------|------|------|
| {未覆盖项1} | {原因} | {计划} |
| {未覆盖项2} | {原因} | {计划} |

---

## 6. 问题记录

### 6.1 测试失败

| 测试 | 失败原因 | 解决方案 | 状态 |
|------|----------|----------|------|
| {测试1} | {原因} | {解决方案} | 已解决 |
| {测试2} | {原因} | {解决方案} | 进行中 |

### 6.2 待改进项

| 改进项 | 优先级 | 预估工时 | 状态 |
|--------|--------|----------|------|
| {改进1} | P1 | 2h | 待开始 |
| {改进2} | P2 | 4h | 待开始 |

---

## 7. CI 集成

### 7.1 GitHub Actions 配置

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
          
      - name: Install Dependencies
        run: composer install --no-progress --prefer-dist
        
      - name: Run Pint
        run: ./vendor/bin/pint --test
        
      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse --no-progress
        
      - name: Run Tests
        run: ./vendor/bin/pest --parallel
```

---

## 8. 签署

| 角色 | 姓名 | 日期 | 签名 |
|------|------|------|------|
| 测试人员 | | | |
| 开发人员 | | | |
| 项目经理 | | | |

---

## 变更记录

| 版本 | 日期 | 作者 | 变更内容 |
|------|------|------|----------|
| 1.0 | YYYY-MM-DD | {姓名} | 初始版本 |
