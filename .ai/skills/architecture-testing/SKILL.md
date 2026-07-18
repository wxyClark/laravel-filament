---
name: architecture-testing
description: "Use this skill for Pest 4 architecture testing in this Laravel + Filament project. Trigger whenever creating or maintaining architecture tests, enforcing DDD layer boundaries, validating naming conventions, or checking dependency rules. Covers: arch() expectations, toExtendNothing(), toHaveSuffix(), toBeFinal(), toNotHaveDependencies(), and custom architecture constraints. Use when user asks to 'add architecture tests', 'enforce DDD rules', 'check layer dependencies', or 'validate code conventions'."
license: MIT
metadata:
  author: laravel-filament
---

# Architecture Testing — 架构测试

> **技术栈**: Laravel 12 + Filament 3.x + Pest 4 + PHP 8.5
> **架构**: DDD 分层 (Domain / Infrastructure / Http)
> **工具**: Pest arch() API

---

## 核心原则

架构测试是代码的"结构守卫"，确保：
- DDD 分层边界不被违反
- 依赖方向正确（Domain ← Infrastructure ← Http）
- 命名约定一致
- 代码质量基线

---

## DDD 分层规则

```
┌─────────────────────────────────────────┐
│  Http Layer                             │
│  - Controllers (thin)                   │
│  - FormRequests                         │
│  - API Resources                        │
│  → 依赖 Domain + Infrastructure         │
├─────────────────────────────────────────┤
│  Infrastructure Layer                   │
│  - Filament Resources                   │
│  - Eloquent Repositories                │
│  - Support Traits                       │
│  → 依赖 Domain                          │
├─────────────────────────────────────────┤
│  Domain Layer                           │
│  - Models, Enums, Services              │
│  - DTOs, Events, Repositories (接口)    │
│  → 只依赖 PHP 标准库 + Domain 内部       │
└─────────────────────────────────────────┘
```

---

## Architecture Test Templates

### 1. DDD Layer Boundary Tests

```php
<?php

// tests/Unit/Architecture/DomainLayerTest.php

arch('Domain layer has no framework dependencies')
    ->expect('App\Domains')
    ->toOnlyDependOn([
        'App\Domains',
        'Illuminate\Support',  // 允许 Carbon, Collection 等
    ]);

arch('Domain models have no Filament dependencies')
    ->expect('App\Domains')
    ->not->toDependOn([
        'Filament',
        'App\Http',
        'App\Infrastructure',
    ]);

arch('Domain services have no HTTP dependencies')
    ->expect('App\Domains\*\Services')
    ->not->toDependOn([
        'Illuminate\Http',
        'App\Http',
    ]);
```

### 2. Infrastructure Layer Tests

```php
<?php

// tests/Unit/Architecture/InfrastructureLayerTest.php

arch('Filament resources have form() and table() methods')
    ->expect('App\Infrastructure\Filament\Resources')
    ->toHaveMethod('form')
    ->toHaveMethod('table');

arch('Repositories implement Domain interfaces')
    ->expect('App\Infrastructure\Repositories')
    ->toImplement('App\Domains\*\Repositories\*Repository');

arch('Filament layer depends on Domain')
    ->expect('App\Infrastructure')
    ->toDependOn('App\Domains')
    ->not->toDependOn('App\Http');
```

### 3. Naming Convention Tests

```php
<?php

// tests/Unit/Architecture/NamingConventionTest.php

arch('Models are singular')
    ->expect('App\Domains\*\Models')
    ->toHaveSuffix('Model');

arch('Enums are named after their domain concept')
    ->expect('App\Domains\*\Enums')
    ->toHaveSuffix('Enum');

arch('Controllers have Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('Services are final')
    ->expect('App\Domains\*\Services')
    ->toBeFinal();

arch('DTOs are readonly')
    ->expect('App\Domains\*\Data')
    ->toBeReadOnly();
```

### 4. Dependency Direction Tests

```php
<?php

// tests/Unit/Architecture/DependencyDirectionTest.php

arch('Domain never depends on Infrastructure')
    ->expect('App\Domains')
    ->not->toDependOn('App\Infrastructure');

arch('Domain never depends on Http')
    ->expect('App\Domains')
    ->not->toDependOn('App\Http');

arch('Infrastructure never depends on Http')
    ->expect('App\Infrastructure')
    ->not->toDependOn('App\Http');

arch('Http depends on both Domain and Infrastructure')
    ->expect('App\Http')
    ->toDependOn('App\Domains')
    ->toDependOn('App\Infrastructure');
```

### 5. Test Structure Tests

```php
<?php

// tests/Unit/Architecture/TestStructureTest.php

arch('Unit tests are isolated')
    ->expect('tests\Unit')
    ->not->toDependOn([
        'Illuminate\Foundation\Testing\RefreshDatabase',
    ]);

arch('Feature tests use RefreshDatabase')
    ->expect('tests\Feature')
    ->toUse('Illuminate\Foundation\Testing\RefreshDatabase');

arch('Filament tests use Livewire')
    ->expect('tests\Feature\Filament')
    ->toDependOn('Livewire\Livewire');
```

---

## Running Architecture Tests

```bash
# Run all architecture tests
./vendor/bin/pest tests/Unit/Architecture/

# Run specific architecture test
./vendor/bin/pest tests/Unit/Architecture/DomainLayerTest.php

# Run with verbose output
./vendor/bin/pest tests/Unit/Architecture/ -v

# Run architecture tests in parallel
./vendor/bin/pest tests/Unit/Architecture/ --parallel
```

---

## Custom Architecture Constraints

### Enforce Service Injection via Constructor

```php
arch('Services use constructor injection')
    ->expect('App\Domains\*\Services')
    ->each->toHaveMethod('__construct');
```

### Enforce No Global State

```php
arch('Tests do not use global state')
    ->expect('tests')
    ->not->toUse('global')
    ->not->toUse('static');
```

### Enforce Immutability

```php
arch('DTOs are immutable')
    ->expect('App\Domains\*\Data')
    ->toBeFinal()
    ->toBeReadOnly();

arch('Value objects are immutable')
    ->expect('App\Domains\*\ValueObjects')
    ->toBeFinal()
    ->toBeReadOnly();
```

---

## Integration with CI

Add to `.github/workflows/ci.yml`:

```yaml
- name: Architecture Tests
  run: ./vendor/bin/pest tests/Unit/Architecture/ --parallel
```

---

## Best Practices

1. **测试独立性**: 每个架构测试只验证一个规则
2. **可读性**: 测试名称清晰描述验证的约束
3. **渐进式**: 先添加宽松规则，逐步收紧
4. **文档化**: 每个测试注释说明为什么这个约束重要
5. **CI 集成**: 架构测试必须在 CI 中运行
