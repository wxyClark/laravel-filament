# 上下文规范：Laravel 12 最佳实践 (Laravel 12 Best Practices)

> **版本**: v3.0 | **层級**: L2 | **最后更新**: 2026-06-07

## 用途说明
确保生成的代码符合 Laravel 12 的最新语法、中间件配置和路由规范。

## 适用场景
- 创建 Controller、Middleware、Service 时
- 配置路由、中间件、服务提供者时
- 编写 API 接口时

## 标准内容块
```markdown
## Laravel 12 开发规范

### 强制要求
1. **路由定义**：使用 `bootstrap/app.php` 注册中间件，不使用 `Kernel.php`
2. **服务提供者**：使用 `bootstrap/providers.php` 注册自定义 Provider
3. **路由风格**：Web 路由使用 `web.php`，API 路由使用 `api.php`，Auth 路由使用 `auth.php`
4. **控制器**：使用构造函数注入，不使用静态方法调用 Facade

### 中间件配置 (bootstrap/app.php)
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->appendToGroup('api', [EnsureJsonApiKeyAuth::class]);
    $middleware->alias([
        'role' => EnsureUserHasRole::class,
        'permission' => EnsureUserHasPermission::class,
    ]);
})
```

### 路由定义
```php
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::resource('products', ProductController::class)
        ->except(['show'])
        ->names('products');
});
```

### 禁止做法
- ❌ 修改 `app/Http/Kernel.php`（Laravel 12 已废弃）
- ❌ 在路由中直接写匿名函数逻辑
- ❌ 使用 `Config::get()`（改用 `config()` 辅助函数）

---

## 项目约定

> ⚠️ 以下内容根据实际项目配置，每个项目需自行调整。

### 目录结构约定
```
app/
├── Domains/              # 领域层
│   ├── Commerce/         # 电商域
│   ├── O2O/              # O2O 域
│   ├── Distribution/     # 分销域
│   └── CRM/              # CRM 域
├── Infrastructure/       # 基础设施层
│   ├── Repositories/     # 仓储实现
│   └── Services/         # 第三方服务集成
├── Application/          # 应用层
│   ├── DTOs/             # 数据传输对象
│   ├── Commands/         # 命令
│   └── Queries/          # 查询
├── Filament/             # Filament 资源
│   └── Resources/        # CRUD 资源
├── Http/                 # 接口层
│   ├── Controllers/      # 控制器
│   ├── Requests/         # 表单验证
│   ├── Resources/        # API 资源
│   ├── Middleware/       # 中间件
│   └── Livewire/         # Livewire 组件
├── Models/               # 模型
├── Events/               # 领域事件
├── Listeners/            # 事件监听器
├── Exceptions/           # 自定义异常
└── Providers/            # 服务提供者
```

### 命名空间约定
| 类型 | 命名空间 | 位置 |
|------|---------|------|
| 领域实体 | `App\Domains\<Domain>\Models` | `app/Domains/<Domain>/Models/` |
| 值对象 | `App\Domains\<Domain>\ValueObjects` | `app/Domains/<Domain>/ValueObjects/` |
| DTO | `App\DTOs\<Domain>` | `app/Application/DTOs/<Domain>/` |
| 事件 | `App\Events\<Domain>` | `app/Events/<Domain>/` |
| 监听器 | `App\Listeners\<Domain>` | `app/Listeners/<Domain>/` |
| Service | `App\Services\<Domain>` | `app/Infrastructure/Services/<Domain>/` |
| Repository | `App\Infrastructure\Repositories\<Domain>` | `app/Infrastructure/Repositories/<Domain>/` |
| Filament Resource | `App\Filament\Resources\<Domain>` | `app/Filament/Resources/<Domain>/` |
| 控制器 | `App\Http\Controllers\<Domain>` | `app/Http/Controllers/<Domain>/` |
| 表单验证 | `App\Http\Requests\<Domain>` | `app/Http/Requests/<Domain>/` |
| 异常 | `App\Exceptions\<Domain>` | `app/Exceptions/<Domain>/` |

### 命名约定
| 类型 | 规则 | 示例 |
|------|------|------|
| 类/文件 | PascalCase | `OrderService`, `CreateOrderData` |
| 方法 | camelCase | `createOrder()`, `handleCallback()` |
| 变量 | camelCase | `$orderItems`, `$customerId` |
| 常量 | SCREAMING_SNAKE_CASE | `MAX_RETRY_COUNT` |
| 数据库表 | 小写复数蛇形 | `order_items`, `product_variants` |
| 数据库字段 | 小写蛇形 | `customer_id`, `created_at` |
| Migration 文件 | 时间戳_动作描述 | `2026_06_07_create_products_table.php` |
| 测试类 | PascalCase + Test | `OrderServiceTest`, `CustomerAuthTest` |
| 测试方法 | snake_case + test | `test_admin_can_create_order()` |

### 编码规范
| 规则 | 要求 |
|------|------|
| 严格类型 | 所有 PHP 文件首行 `declare(strict_types=1);` |
| 返回类型 | 所有方法必须声明返回类型（含 `void`） |
| 只读类 | DTO 必须使用 `readonly class` |
| DI | 通过构造函数注入，构造函数参数不超过 4 个 |
| 异常 | 业务异常继承 `RuntimeException`，置于 `App\Exceptions` |
| 事件命名 | 过去式：`OrderCreated` 而非 `CreateOrder` |
| 金额存储 | `DECIMAL(12,4)`，代码中使用 `bcmath` |
| 状态管理 | 使用 PHP Enum 或 `spatie/laravel-model-states` |
| 软删除 | 所有记录表必须包含 `softDeletes()` |
| 时间戳 | 所有表必须包含 `timestamps()` |

### 测试规范
| 规则 | 要求 |
|------|------|
| 框架 | 使用 Pest PHP |
| 测试位置 | `tests/Feature/<Domain>/` 或 `tests/Unit/<Domain>/` |
| 测试命名 | `test_<场景>_<操作>_<预期结果>()` |
| 数据工厂 | 使用 Factory + States，不硬编码测试数据 |
| 事务回滚 | 使用 `RefreshDatabase` trait 或 `DatabaseTransactions` |
| 覆盖率目标 | 核心 Service ≥ 90%，Model ≥ 80%，Controller ≥ 70% |

### 队列规范
| 规则 | 要求 |
|------|------|
| 异步任务 | 实现 `ShouldQueue` 接口 |
| 失败处理 | 实现 `failed()` 方法记录日志 |
| 重试次数 | 默认 3 次，关键任务 5 次 |
| 幂等性 | 所有队列任务必须支持重复执行 |

### 日志规范
| 级别 | 使用场景 |
|------|---------|
| `info` | 正常业务流程记录（订单创建、用户注册） |
| `warning` | 可恢复的异常情况（库存不足、重试中） |
| `error` | 不可恢复的错误（支付失败、外部 API 异常） |
| `debug` | 开发调试（不用于生产） |

---

## 项目基线配置

> 以下为本项目实际配置，新任务组装时可参考。

| 项目 | 配置 |
|------|------|
| 框架版本 | Laravel 12.x |
| 后台框架 | Filament 3.x |
| PHP 版本 | 8.4+ |
| 数据库 | MySQL 8.0 |
| 缓存 | Redis |
| 队列 | Horizon |
| 认证 | 多守卫 (customer/admin) + Sanctum |
| 权限 | spatie/laravel-permission |
| 代码风格 | Pint (Laravel preset) |
| 静态分析 | PHPStan Level 5 |
| 测试 | Pest PHP |
```
```

---

**版本**: v3.0 | **最后更新**: 2026-06-07
