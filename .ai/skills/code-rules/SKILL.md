---
name: code-rules
description: "Comprehensive code standards for this Laravel + Filament project. Covers: file placement, call chain, class member order, naming, testing conventions, and reuse patterns. Based on Laravel official best practices and Chinese top-tier PHP company standards (Alibaba/Tencent/ByteDance style). Use whenever writing, reviewing, or refactoring code."
license: MIT
metadata:
  author: laravel-filament
---

# 代码规范 Rules — 统一风格手册

> **技术栈**: Laravel 12 + Filament 3.x + PHP 8.5 + Pest + DDD
> **原则**: 约定优于配置、单一职责、依赖注入、可测试、可复用

---

## 一、文件存放规则

### 1.1 DDD 分层目录

```
app/
├── Domains/                          # 领域层（核心业务）
│   └── {Domain}/                     # 按业务域划分
│       ├── Models/                   # Eloquent 模型
│       ├── Enums/                    # 状态枚举 (BackedEnum)
│       ├── Services/                 # 领域服务（业务逻辑）
│       ├── Data/                     # DTO 值对象 (readonly class)
│       ├── Events/                   # 领域事件
│       ├── Repositories/             # 仓储接口 (Interface)
│       └── Policies/                 # 权限策略
│
├── Infrastructure/                   # 基础设施层（框架适配）
│   ├── Filament/                     # Filament 后台
│   │   ├── Resources/{Domain}/       # 按域分组的 CRUD 资源
│   │   │   └── {Entity}Resource.php
│   │   │   └── Pages/               # List/Create/Edit/View
│   │   ├── Widgets/                  # 小组件
│   │   └── Pages/                    # 自定义页面
│   ├── Repositories/Eloquent/        # 仓储实现
│   └── Support/Traits/              # 公共 Trait
│
├── Http/                             # 接入层（请求/响应）
│   ├── Controllers/Api/              # API 控制器
│   ├── Controllers/Api/Auth/         # 认证控制器
│   ├── Requests/                     # FormRequest
│   └── Resources/                    # API Resource
│
├── Models/                           # 跨域共享模型（仅此放）
└── Services/                         # 共享基础设施服务
```

### 1.2 文件放置决策表

| 文件类型 | 存放位置 | 说明 |
|---------|---------|------|
| Eloquent Model | `Domains/{Domain}/Models/` | 核心业务模型 |
| 共享 Model | `app/Models/` | 跨域共享的模型 |
| 状态枚举 | `Domains/{Domain}/Enums/` | `OrderStatus::class` |
| 业务逻辑 | `Domains/{Domain}/Services/` | Service 层 |
| DTO | `Domains/{Domain}/Data/` | `readonly class` |
| 仓储接口 | `Domains/{Domain}/Repositories/` | Interface |
| 仓储实现 | `Infrastructure/Repositories/Eloquent/` | implements Interface |
| Filament 资源 | `Infrastructure/Filament/Resources/{Domain}/` | CRUD Resource |
| API 控制器 | `Http/Controllers/Api/` | 瘦控制器 |
| 表单验证 | `Http/Requests/{Domain}/` | FormRequest |
| API 资源 | `Http/Resources/` | JsonResource |
| 权限策略 | `Domains/{Domain}/Policies/` | Policy |
| 领域事件 | `Domains/{Domain}/Events/` | Event 类 |
| Trait | `Infrastructure/Support/Traits/` | 公共 Trait |
| 配置文件 | `config/` | 不在其他地方 |

---

## 二、调用链路规则

### 2.1 标准调用方向

```
Http (Controller) → Domain (Service) → Domain (Repository Interface)
                                           ↓
                                    Infrastructure (Repository Eloquent)
                                           ↓
                                      Domain (Model)
```

### 2.2 禁止方向

| 从 | 到 | 原因 |
|----|-----|------|
| Domain | Http | 领域层不应依赖 HTTP |
| Domain | Filament | 领域层不应依赖展示层 |
| Infrastructure | Http | 基础设施不应依赖接入层 |
| Filament Resource | Domain Service | Resource 只做展示，业务逻辑在 Service |
| Controller | Eloquent | Controller 应调用 Service |

### 2.3 正确调用链示例

```php
// ✅ Controller → Service → Repository
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            OrderCreateData::fromArray($request->validated())
        );

        return response()->json([
            'data' => new OrderResource($order),
        ], 201);
    }
}

// ✅ Service → Repository Interface (不直接用 Model)
class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    public function create(OrderCreateData $data): Order
    {
        $order = $this->orderRepository->create($data->toArray());
        $this->eventDispatcher->dispatch(new OrderCreated($order));
        return $order;
    }
}

// ❌ Controller 直接操作 Eloquent
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $order = Order::create($request->all());  // 禁止！
        Mail::to($order->customer)->send(new OrderMail());  // 禁止！
        return response()->json($order);
    }
}
```

### 2.4 Filament 调用链

```
Filament Resource → Domain Service (通过 Action 调用)
Filament Resource → Domain Model (只读展示)
Filament Widget → Domain Service/Model (统计查询)
Filament Page → Domain Service (复杂操作)
```

```php
// ✅ Filament Resource 只做展示配置
class OrderResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('order_number')->required(),
            Select::make('status')->options(OrderStatus::class),
        ]);
    }

    // 业务逻辑在 Service 中
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['order_number'] = app(OrderNumberService::class)->generate();
        return $data;
    }
}
```

---

## 三、类成员排序规则

所有 PHP 类必须遵循以下成员排序（Pint 强制执行）：

```php
class ExampleClass
{
    // 1. Trait 导入
    use HasFactory;
    use SoftDeletes;

    // 2. 常量
    const STATUS_ACTIVE = 'active';

    // 3. 属性（按可见性）
    public string $name;
    protected int $count;
    private string $secret;

    // 4. 构造函数
    public function __construct(
        private readonly string $value
    ) {}

    // 5. 析构函数
    public function __destruct() {}

    // 6. 魔术方法
    public function __toString(): string {}

    // 7. 公开方法（按字母排序）
    public function active(): static {}
    public function getFullName(): string {}

    // 8. 受保护方法
    protected function process(): void {}

    // 9. 私有方法
    private function validate(): bool {}
}
```

---

## 四、命名规范

### 4.1 Laravel 标准命名

| 类型 | 约束 | 示例 | 禁止 |
|------|------|------|------|
| Controller | 单数 + `Controller` | `OrderController` | `OrdersController` |
| Model | 单数 PascalCase | `Order` | `Orders` |
| Service | 单数 + `Service` | `OrderService` | `OrderServices` |
| Repository Interface | 单数 + `RepositoryInterface` | `OrderRepositoryInterface` | |
| DTO | 动词过去式或名词 | `OrderCreateData` | |
| Enum | 单数 PascalCase | `OrderStatus` | `OrderStatuses` |
| Event | 动词过去式 | `OrderCreated` | `CreateOrder` |
| Policy | 单数 + `Policy` | `OrderPolicy` | |
| FormRequest | `动词` + 实体 + `Request` | `StoreOrderRequest` | |
| API Resource | 单数 + `Resource` | `OrderResource` | |
| Migration | `动词` + 表名 | `create_orders_table` | |
| Table | 复数 snake_case | `orders` | `order` |
| Column | snake_case | `order_number` | `orderNumber` |
| Foreign key | `{model}_id` | `customer_id` | `customers_id` |

### 4.2 方法命名

| 场景 | 约束 | 示例 |
|------|------|------|
| 获取单个 | `get{Entity}` 或 `find` | `getOrder()` / `find()` |
| 获取列表 | `get{Entities}` 或 `query` | `getOrders()` / `query()` |
| 创建 | `create` | `create(array $data)` |
| 更新 | `update` | `update(Model $model, array $data)` |
| 删除 | `delete` 或 `destroy` | `delete(Model $model)` |
| 判断 | `is` / `has` / `can` | `isActive()` / `hasPermission()` |
| Scope | `scope{Name}` | `scopeActive()` |
| 事件处理 | `handle` 或 `on{Event}` | `handle(OrderCreated $event)` |

---

## 五、代码结构规范

### 5.1 每个文件只做一件事

```php
// ✅ 单一职责：只处理订单创建
class CreateOrderService
{
    public function create(OrderCreateData $data): Order {}
}

// ❌ 多职责：又创建又发邮件又扣库存
class OrderService
{
    public function createAndNotifyAndDeduct(array $data): Order {}
}
```

### 5.2 方法不超过 20 行

超过 20 行的方法必须拆分：

```php
// ❌ 过长
public function process(Order $order): void
{
    // 50 行代码...
}

// ✅ 拆分为私有方法
public function process(Order $order): void
{
    $this->validateOrder($order);
    $this->calculateTotal($order);
    $this->applyDiscounts($order);
    $this->saveOrder($order);
    $this->notifyCustomer($order);
}

private function validateOrder(Order $order): void {}
private function calculateTotal(Order $order): void {}
private function applyDiscounts(Order $order): void {}
private function saveOrder(Order $order): void {}
private function notifyCustomer(Order $order): void {}
```

### 5.3 类不超过 200 行

超过 200 行的类必须拆分：

```php
// ❌ 上帝类
class EverythingService { /* 500 行 */ }

// ✅ 拆分为专用服务
class OrderValidationService {}
class OrderCalculationService {}
class OrderNotificationService {}
```

### 5.4 构造函数注入（禁止 app()）

```php
// ✅ 依赖注入
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}
}

// ❌ 使用 app() 辅助函数
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $service = app(OrderService::class);  // 禁止
    }
}
```

---

## 六、Model 规范

### 6.1 属性声明顺序

```php
class Order extends Model
{
    // 1. 使用的 Trait
    use HasFactory, SoftDeletes;

    // 2. 表名（如果非标准）
    protected $table = 'orders';

    // 3. 主键类型（如果非标准）
    protected $keyType = 'string';
    public $incrementing = false;

    // 4. Guard 属性
    protected $guard = 'admin';

    // 5. 填充字段
    protected $fillable = [
        'customer_id',
        'status',
        'total_amount',
    ];

    // 6. 隐藏字段
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 7. 类型转换
    protected $casts = [
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'password' => 'hashed',
    ];

    // 8. 关系方法（按字母排序）
    public function customer(): BelongsTo {}
    public function items(): HasMany {}

    // 9. Scope 方法（按字母排序）
    public function scopeActive($query) {}
    public function scopePending($query) {}
}
```

### 6.2 关系方法必须声明返回类型

```php
// ✅ 有返回类型
public function customer(): BelongsTo
{
    return $this->belongsTo(Customer::class);
}

public function items(): HasMany
{
    return $this->hasMany(OrderItem::class);
}

// ❌ 无返回类型
public function customer()
{
    return $this->belongsTo(Customer::class);
}
```

### 6.3 Scope 方法

```php
// ✅ Scope 使用 query 参数 + Builder 返回类型
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}

public function scopeByStatus(Builder $query, string $status): Builder
{
    return $query->where('status', $status);
}
```

---

## 七、Service 规范

### 7.1 Service 类模板

```php
<?php

declare(strict_types=1);

namespace App\Domains\Trade\Services;

use App\Domains\Trade\Data\OrderCreateData;
use App\Domains\Trade\Models\Order;
use App\Domains\Trade\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    public function create(OrderCreateData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepository->create($data->toArray());

            $this->dispatchEvent(new OrderCreated($order));

            return $order;
        });
    }

    public function cancel(Order $order): Order
    {
        abort_if(
            ! in_array($order->status, ['pending', 'paid']),
            422,
            '当前状态不允许取消'
        );

        return $this->orderRepository->update($order, [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    private function dispatchEvent(object $event): void
    {
        event($event);
    }
}
```

### 7.2 Service 规则

- 构造函数只注入依赖
- 每个公开方法只做一件事
- 使用 `DB::transaction()` 包裹多表操作
- 返回 Model 或 DTO，不返回数组
- 异常使用 `abort()` 或自定义 Exception
- 私有方法提取复杂逻辑

---

## 八、DTO 规范

### 8.1 DTO 类模板

```php
<?php

declare(strict_types=1);

namespace App\Domains\Trade\Data;

use Illuminate\Contracts\Support\Arrayable;

readonly class OrderCreateData implements Arrayable
{
    public function __construct(
        public int $customerId,
        /** @var OrderItemData[] */
        public array $items,
        public ?string $shippingAddress = null,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerId: (int) $data['customer_id'],
            items: array_map(
                fn (array $item) => OrderItemData::fromArray($item),
                $data['items']
            ),
            shippingAddress: $data['shipping_address'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'shipping_address' => $this->shippingAddress,
            'notes' => $this->notes,
        ];
    }
}
```

### 8.2 DTO 规则

- 使用 `readonly class` (PHP 8.2+)
- 实现 `Arrayable` 接口
- 提供 `fromArray()` 静态工厂方法
- 属性使用 camelCase
- 不包含验证逻辑
- 不包含业务逻辑

---

## 九、Controller 规范

### 9.1 API Controller 模板

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Domains\Trade\Models\Order;
use App\Domains\Trade\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::latest()->paginate(
            $request->integer('per_page', 20)
        );

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            OrderCreateData::fromArray($request->validated())
        );

        return response()->json([
            'data' => new OrderResource($order),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'data' => new OrderResource($order->load('items', 'customer')),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->update($order, $request->validated());

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->orderService->delete($order);

        return response()->json(null, 204);
    }
}
```

### 9.2 Controller 规则

- 只调用 Service，不直接操作 Eloquent
- 使用 FormRequest 做验证
- 使用 API Resource 做格式化
- 返回类型声明
- 方法不超过 10 行
- 不包含业务逻辑

---

## 十、测试规范

### 10.1 测试目录结构

```
tests/
├── Unit/
│   └── Domains/{Domain}/
│       ├── Models/{Entity}Test.php        # 模型测试
│       ├── Services/{Service}Test.php     # Service 测试
│       └── Data/{Dto}Test.php             # DTO 测试
├── Feature/
│   ├── Api/
│   │   ├── Auth/
│   │   │   ├── JwtAuthTest.php            # JWT 认证测试
│   │   │   └── SessionAuthTest.php        # Session 认证测试
│   │   └── {Entity}ApiTest.php            # API 接口测试
│   └── Filament/
│       └── Auth/
│           └── SessionAuthTest.php        # Filament 认证测试
└── Pest.php
```

### 10.2 测试命名规则

```php
// ✅ test() 风格（推荐）
test('admin can create order', function () {});
test('order total is calculated correctly', function () {});
test('unauthenticated user cannot access orders', function () {});

// ❌ it() 风格（不推荐）
it('can create order', function () {});
```

### 10.3 测试 AAA 模式

```php
test('create order deducts stock', function () {
    // Arrange
    $product = Product::factory()->create(['stock' => 10]);
    $customer = Customer::factory()->create();
    $dto = new OrderCreateData(
        customerId: $customer->id,
        items: [new OrderItemData(productId: $product->id, quantity: 2)]
    );

    // Act
    $order = app(OrderService::class)->create($dto);

    // Assert
    expect($order->status)->toBe('pending');
    expect($product->refresh()->stock)->toBe(8);
});
```

### 10.4 测试断言规范

```php
// ✅ 使用具体断言
$response->assertSuccessful();
$response->assertCreated();
$response->assertNotFound();
$response->assertForbidden();
$response->assertUnprocessable();
$this->assertModelExists($order);
$this->assertSoftDeleted('orders', ['id' => $order->id]);

// ❌ 使用 assertStatus()
$response->assertStatus(200);
$response->assertStatus(201);
$response->assertStatus(404);
```

### 10.5 Filament 测试模板

```php
use Livewire\Livewire;
use Filament\Actions\DeleteAction;

test('admin can list orders', function () {
    $admin = Admin::factory()->create();
    $orders = Order::factory()->count(5)->create();

    Livewire::test(ListOrders::class)
        ->loginAs($admin)
        ->assertCanSeeTableRecords($orders);
});

test('admin can create order', function () {
    $admin = Admin::factory()->create();

    Livewire::test(CreateOrder::class)
        ->loginAs($admin)
        ->fillForm([
            'order_number' => 'ORD-001',
            'status' => 'pending',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('orders', ['order_number' => 'ORD-001']);
});
```

---

## 十一、Enum 规范

```php
<?php

declare(strict_types=1);

namespace App\Domains\Trade\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待付款',
            self::PAID => '已付款',
            self::SHIPPED => '已发货',
            self::DELIVERED => '已送达',
            self::CANCELLED => '已取消',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'info',
            self::SHIPPED => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }
}
```

---

## 十二、FormRequest 规范

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => '客户不能为空',
            'items.required' => '订单项不能为空',
            'items.min' => '至少需要一个订单项',
        ];
    }
}
```

---

## 十三、API Resource 规范

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'total_amount' => $this->total_amount,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

---

## 十四、检查清单

开发新功能时，逐项检查：

### 文件层面
- [ ] 文件放在正确的 DDD 目录
- [ ] 文件名符合命名规范
- [ ] `declare(strict_types=1)` 存在
- [ ] 命名空间正确
- [ ] use 语句按字母排序

### 类层面
- [ ] 类成员顺序正确（trait → constant → property → constructor → method）
- [ ] 类不超过 200 行
- [ ] 每个方法只做一件事
- [ ] 构造函数注入依赖

### 方法层面
- [ ] 方法不超过 20 行
- [ ] 有返回类型声明
- [ ] 参数有类型声明
- [ ] 不使用 `app()` 辅助函数

### 测试层面
- [ ] Service 方法有单元测试
- [ ] API 端点有集成测试
- [ ] Filament 操作有功能测试
- [ ] 使用 AAA 模式
- [ ] 使用具体断言（非 assertStatus）

### 代码质量
- [ ] Pint 通过 (`./vendor/bin/pint --test`)
- [ ] PHPStan 通过 (`./vendor/bin/phpstan analyse`)
- [ ] Pest 全部通过 (`./vendor/bin/pest --compact`)
