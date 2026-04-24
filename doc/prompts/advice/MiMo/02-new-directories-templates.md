# 📁 新增目录卡片模板

> **基于评估报告的补全方案**  
> **创建日期**: 2026-04-24  
> **目标**: 创建 `cards/05-ops/`、`cards/06-security/`、`cards/07-testing/` 目录及卡片

---

## 一、05-ops/ 运维相关卡片

### 1.1 monitoring-telescope.md

```markdown
# 运维规范：Laravel Telescope 监控配置

## 用途说明
配置本地开发和生产环境的应用监控，实现异常追踪和性能分析。

## 适用场景
- 本地开发调试
- 生产环境异常排查
- 性能瓶颈分析
- 队列任务监控

## 标准内容块
```markdown
## Telescope 监控配置

### 安装与配置
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 监控范围
1. **Requests**: 所有 HTTP 请求
2. **Commands**: Artisan 命令执行
3. **Exceptions**: 异常捕获
4. **Jobs**: 队列任务执行
5. **Queries**: 数据库查询（记录慢查询 > 100ms）

### 环境限制
```php
// config/telescope.php
'env' => ['local', 'staging'],
```

### 自定义过滤器
```php
// 记录特定通道的日志
Telescope::filter(function (Entry $entry) {
    if ($entry->type === 'job') {
        return in_array($entry->content['queue'], ['payments', 'notifications']);
    }
    return true;
});
```

### 性能优化
- 生产环境建议使用采样率：`Telescope::sampleRate(10);`
- 慢查询阈值设置：`'slow' => 100`（毫秒）
```
```

---

### 1.2 queue-horizon.md

```markdown
# 运维规范：Laravel Horizon 队列监控

## 用途说明
配置队列工作者的监控和管理，确保异步任务的可靠执行。

## 适用场景
- 邮件发送队列
- 支付回调处理
- 报表生成任务
- 文件处理任务

## 标准内容块
```markdown
## Horizon 配置

### 安装
```bash
composer require laravel/horizon
php artisan horizon:install
```

### 队列配置 (config/horizon.php)
```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'payments', 'notifications'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 10,
            'maxTime' => 3600,
            'maxJobs' => 500,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 90,
            'nice' => 0,
        ],
    ],
],
```

### 失败任务处理
```bash
# 查看失败任务
php artisan horizon:failed

# 重试失败任务
php artisan horizon:retry {id}

# 清理失败任务
php artisan horizon:prune --hours=48
```

### 监控指标
- **Pending**: 等待执行的任务
- **Completed**: 已完成任务
- **Failed**: 失败任务
- **Processes**: 活跃进程数
- **Queue Wait Time**: 队列等待时间

### 告警配置
```php
// app/Providers/AppServiceProvider.php
Horizon::night();
```
```
```

---

### 1.3 deployment-checklist.md

```markdown
# 运维规范：部署检查清单

## 用途说明
确保每次部署前完成所有必要的检查，降低部署风险。

## 适用场景
- 生产环境部署
- 预发布环境发布
- 数据库迁移执行

## 标准内容块
```markdown
## 部署前检查清单

### 代码检查
- [ ] 所有测试通过 (`php artisan test`)
- [ ] 代码风格检查通过 (`./vendor/bin/pint --test`)
- [ ] 静态分析通过 (`./vendor/bin/phpstan analyse`)
- [ ] 没有 `dd()` 或 `dump()` 残留

### 数据库检查
- [ ] 迁移文件已准备好 (`php artisan migrate --pretend`)
- [ ] 回滚脚本已测试 (`php artisan migrate:rollback`)
- [ ] 种子数据已准备（如需要）

### 环境配置
- [ ] `.env` 文件已更新
- [ ] 缓存已清理 (`php artisan config:clear`)
- [ ] 路由已缓存 (`php artisan route:cache`)
- [ ] 视图已缓存 (`php artisan view:cache`)

### 队列与任务
- [ ] Horizon 已重启 (`php artisan horizon:terminate`)
- [ ] 计划任务已配置 (`php artisan schedule:run`)
- [ ] 失败任务已处理

### 回滚准备
- [ ] 数据库备份已完成
- [ ] 代码版本已标记
- [ ] 回滚脚本已准备

## 部署命令
```bash
# 零停机部署
php artisan down --refresh=15 --retry=60
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate
php artisan up
```
```
```

---

## 二、06-security/ 安全相关卡片

### 2.1 auth-sanctum.md

```markdown
# 安全规范：API 认证配置 (Sanctum)

## 用途说明
配置基于 Token 的 API 认证，保护接口安全。

## 适用场景
- 移动端 API 认证
- SPA 前端认证
- 第三方系统集成

## 标准内容块
```markdown
## Sanctum 配置

### 安装
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 模型配置
```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

### Token 生成
```php
// 创建 Token
$token = $user->createToken('api-token')->plainTextToken;

// 带能力限制
$token = $user->createToken('api-token', ['read', 'write'])->plainTextToken;
```

### 中间件配置
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

### 安全建议
- Token 有效期设置合理（建议 24 小时）
- 支持 Token 撤销（`$user->tokens()->delete()`）
- 敏感操作需要重新验证
```
```

---

### 2.2 authorization-gate.md

```markdown
# 安全规范：权限控制 (Gates & Policies)

## 用途说明
实现细粒度的权限控制，确保用户只能访问授权资源。

## 适用场景
- 后台管理权限
- API 资源权限
- Filament Action 权限

## 标准内容块
```markdown
## 权限控制配置

### Gate 定义 (app/Providers/AuthServiceProvider.php)
```php
use App\Models\User;

Gate::define('edit-posts', function (User $user) {
    return in_array($user->role, ['admin', 'editor']);
});

Gate::define('delete-post', function (User $user, Post $post) {
    return $user->id === $post->user_id || $user->role === 'admin';
});
```

### Policy 定义
```php
// app/Policies/PostPolicy.php
class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

### Filament 权限集成
```php
// 在 Resource 中
public static function canAccess(): bool
{
    return auth()->user()->can('viewAny', Post::class);
}

public static function table(Table $table): Table
{
    return $table
        ->actions([
            EditAction::make()
                ->visible(fn (Post $record): bool => 
                    auth()->user()->can('update', $record)
                ),
            DeleteAction::make()
                ->visible(fn (Post $record): bool => 
                    auth()->user()->can('delete', $record)
                ),
        ]);
}
```
```
```

---

### 2.3 sql-injection-prevention.md

```markdown
# 安全规范：SQL 注入防护

## 用途说明
防止 SQL 注入攻击，确保数据库查询安全。

## 适用场景
- 用户输入查询
- 动态排序/筛选
- 原生 SQL 查询

## 标准内容块
```markdown
## SQL 注入防护规则

### ✅ 正确做法：参数绑定
```php
// Eloquent（自动绑定）
$users = User::where('email', $email)->get();

// Query Builder（自动绑定）
$users = DB::table('users')
    ->where('email', '=', $email)
    ->get();

// 原生查询（手动绑定）
$users = DB::select(
    'SELECT * FROM users WHERE email = ? AND status = ?',
    [$email, $status]
);

// 命名绑定
$users = DB::select(
    'SELECT * FROM users WHERE email = :email AND status = :status',
    ['email' => $email, 'status' => $status]
);
```

### ❌ 禁止做法：字符串拼接
```php
// 危险！禁止使用
$users = DB::select("SELECT * FROM users WHERE email = '$email'");
$users = DB::select("SELECT * FROM users ORDER BY $column");
```

### 动态排序安全处理
```php
// 白名单验证
$allowedColumns = ['id', 'name', 'created_at'];
$column = in_array($request->sort, $allowedColumns) ? $request->sort : 'id';
$direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'asc';

$users = User::orderBy($column, $direction)->get();
```

### Filament 筛选器安全
```php
// 使用 Filament 内置筛选器（自动安全）
SelectFilter::make('status')
    ->options([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ])
    ->query(function (Builder $query, array $data) {
        return $query->when($data['status'], function ($q, $status) {
            $q->where('status', $status);
        });
    });
```
```
```

---

## 三、07-testing/ 测试专项卡片

### 3.1 pest-unit-test.md

```markdown
# 测试模板：Pest 单元测试

## 用途说明
编写简洁的单元测试，验证单一功能的正确性。

## 适用场景
- Service 类方法测试
- Value Object 测试
- Helper 函数测试

## 标准内容块
```markdown
# Pest 单元测试模板

## 文件位置
`tests/Unit/Services/{ServiceName}Test.php`

## 示例代码
```php
<?php

use App\Services\OrderService;
use App\Models\Order;
use App\Models\Customer;

describe('OrderService', function () {
    beforeEach(function () {
        $this->service = new OrderService();
    });

    it('calculates order total correctly', function () {
        $items = [
            ['price' => 100, 'quantity' => 2],
            ['price' => 50, 'quantity' => 3],
        ];

        $total = $this->service->calculateTotal($items);

        expect($total)->toBe(350);
    });

    it('throws exception when order is empty', function () {
        expect(fn () => $this->service->calculateTotal([]))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('applies discount correctly', function () {
        $order = Order::factory()->create(['total' => 1000]);
        
        $discounted = $this->service->applyDiscount($order, 10); // 10% off

        expect($discounted)->toBe(900);
    });
});
```

## 测试结构
```php
describe('Feature Name', function () {
    beforeEach(function () {
        // Setup
    });

    afterEach(function () {
        // Cleanup
    });

    it('expected behavior', function () {
        // Given
        // When
        // Then
    });
});
```

## 常用断言
```php
expect($value)->toBe($expected);
expect($value)->toBeTrue();
expect($collection)->toHaveCount(3);
expect($collection)->toContain($item);
expect($fn)->toThrow(Exception::class);
```
```
```

---

### 3.2 pest-feature-test.md

```markdown
# 测试模板：Pest 功能测试

## 用途说明
编写功能测试，验证完整业务流程和 HTTP 请求。

## 适用场景
- API 接口测试
- Filament 页面测试
- 完整业务流程测试

## 标准内容块
```markdown
# Pest 功能测试模板

## 文件位置
`tests/Feature/Api/OrderApiTest.php`

## 示例代码
```php
<?php

use App\Models\User;
use App\Models\Order;

describe('Order API', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('can list orders', function () {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    it('can create an order', function () {
        $data = [
            'items' => [
                ['product_id' => 1, 'quantity' => 2],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'status', 'total']]);
        
        $this->assertDatabaseHas('orders', ['user_id' => $this->user->id]);
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    });
});
```

## Filament 页面测试
```php
<?php

use App\Filament\Resources\OrderResource;

it('can render the order list page', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(OrderResource::getUrl('index'))
        ->assertSuccessful();
});

it('can create an order', function () {
    $newData = Order::factory()->make();

    $this->actingAs(User::factory()->admin()->create())
        ->callLivewire(OrderResource::getUrl('create'), [
            'data' => $newData->toArray(),
        ])
        ->assertHasNoFormErrors();
});
```
```
```

---

### 3.3 test-data-factory.md

```markdown
# 测试模板：数据工厂 (Model Factories)

## 用途说明
使用 Factory 快速生成测试数据，保持测试数据的一致性和可维护性。

## 适用场景
- 单元测试数据准备
- 功能测试数据准备
- 性能测试数据生成

## 标准内容块
```markdown
# Model Factory 模板

## 文件位置
`database/factories/{Model}Factory.php`

## 示例代码
```php
<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(10),
            'slug' => fake()->unique()->slug(),
            'content' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(['draft', 'published']),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
```

## 使用方式
```php
// 创建单个
$post = Post::factory()->create();

// 批量创建
$posts = Post::factory()->count(10)->create();

// 使用状态
$draftPost = Post::factory()->draft()->create();
$publishedPost = Post::factory()->published()->create();

// 覆盖属性
$post = Post::factory()->create([
    'title' => 'Custom Title',
    'user_id' => $user->id,
]);

// 关联创建
$user = User::factory()
    ->has(Post::factory()->count(3))
    ->create();
```
```
```

---

## 四、使用说明

### 目录创建命令

```bash
# 创建新目录
mkdir -p doc/prompts/cards/05-ops
mkdir -p doc/prompts/cards/06-security
mkdir -p doc/prompts/cards/07-testing

# 创建卡片文件（将上述内容保存到对应文件）
```

### 更新母提示词索引

在 `usage-demo/02-meta-prompt-template.md` 中添加：

```markdown
### 05-ops/ (运维)
- `monitoring-telescope.md`: 本地调试与异常追踪
- `queue-horizon.md`: 队列监控与失败重试
- `deployment-checklist.md`: 部署检查清单

### 06-security/ (安全)
- `auth-sanctum.md`: API认证配置
- `authorization-gate.md`: 权限控制
- `sql-injection-prevention.md`: SQL注入防御

### 07-testing/ (测试)
- `pest-unit-test.md`: 单元测试模板
- `pest-feature-test.md`: 功能测试模板
- `test-data-factory.md`: 测试数据工厂
```

---

**模板创建完成** | **版本**: v1.0 | **创建者**: MiMo
