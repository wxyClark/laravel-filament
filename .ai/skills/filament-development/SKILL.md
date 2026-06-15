# Laravel Filament 3.x 开发代码规范

> 基于 Laravel 12 + Filament 3.x + DDD 架构的企业级开发规范。适用于 `laravel-filament` 项目。

---

## 一、项目架构规范

### 1.1 DDD 目录结构

```
app/
├── Domains/                      # 领域层（核心业务逻辑）
│   ├── User/                     # 用户域
│   │   ├── Models/               # 领域模型
│   │   │   ├── Admin.php
│   │   │   └── Customer.php
│   │   ├── Services/             # 领域服务
│   │   │   └── AuthService.php
│   │   ├── Data/                 # DTO 值对象
│   │   │   └── LoginData.php
│   │   ├── Events/               # 领域事件
│   │   │   └── UserRegistered.php
│   │   ├── Enums/                # 领域枚举
│   │   │   └── CustomerStatus.php
│   │   └── Repositories/         # 仓储接口
│   │       └── CustomerRepository.php
│   ├── Product/                  # 商品域
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Data/
│   │   └── Events/
│   ├── Trade/                    # 交易域
│   ├── O2O/                      # 预约域
│   ├── Distribution/             # 分销域
│   └── CRM/                      # CRM 域
│
├── Infrastructure/               # 基础设施层
│   ├── Filament/                 # Filament 资源（展示层）
│   │   ├── Panels/               # 面板配置
│   │   │   └── AdminPanelProvider.php
│   │   ├── Resources/            # 资源类
│   │   │   └── [业务域]/         # 按业务域分组
│   │   │       └── [实体]Resource/
│   │   │           ├── Pages/    # 页面（List/Edit/Create/View）
│   │   │           ├── Forms/    # 表单组件
│   │   │           ├── Tables/   # 表格组件
│   │   │           └── RelationManagers/
│   │   ├── Widgets/              # 小组件
│   │   ├── Pages/                # 自定义页面
│   │   ├── Clusters/             # 资源集群
│   │   └── Plugins/              # 自定义插件
│   ├── Repositories/             # 仓储具体实现
│   │   └── Eloquent/
│   ├── Services/                 # 基础设施服务
│   │   └── External/             # 第三方服务集成
│   └── Support/                  # 通用支持
│       ├── Traits/               # 公共 Trait
│       ├── Helpers/              # 辅助函数
│       └── DTO/                  # 通用 DTO
│
├── Http/                         # 接入层
│   ├── Controllers/              # API/Web 控制器（瘦控制器）
│   ├── Middleware/               # 中间件
│   └── Requests/                 # 表单请求验证
│
├── Models/                       # 全局模型（仅放跨域共享的）
└── Providers/                    # 服务提供者

database/
├── migrations/                   # 数据库迁移
├── seeders/                      # 数据填充
└── factories/                    # 数据工厂

tests/
├── Unit/Domains/                 # 领域层单元测试
├── Feature/Filament/             # Filament 功能测试
└── Pest.php
```

### 1.2 各层职责边界

| 层级 | 职责 | 禁止事项 |
|------|------|---------|
| **Domain** | 核心业务逻辑、实体、值对象、领域事件、仓储接口 | 不依赖任何框架类 |
| **Infrastructure** | 框架适配、Filament 展示、仓储实现、第三方服务 | 不包含业务规则 |
| **Http** | 请求接收、参数验证、响应返回 | 不包含业务逻辑 |

### 1.3 业务逻辑与展示逻辑分离原则

**核心原则：Filament 层只做展示，业务逻辑全部下沉到 Domain 层。**

```php
// ✅ 正确：Resource 中只定义展示配置
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('订单信息')
                ->schema([
                    TextInput::make('order_number')
                        ->required()
                        ->maxLength(50),
                    Select::make('status')
                        ->options(OrderStatus::class),
                ]),
        ]);
    }
}

// ❌ 错误：Resource 中包含业务逻辑
class OrderResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('订单信息')
                ->schema([
                    // 错误：不应该在这里扣减库存！
                    TextInput::make('quantity')
                        ->live(after: fn (callable $set) => StockService::deduct($set['quantity'])),
                ]),
        ]);
    }
}

// ✅ 正确：业务逻辑在 Service 中
class OrderService
{
    public function create(OrderCreateData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data->toArray());
            $this->stockService->deduct($data->items);
            $this->eventDispatcher->dispatch(new OrderCreated($order));
            return $order;
        });
    }
}
```

### 1.4 Filament 插件开发标准

**目录结构：**
```
app/Infrastructure/Filament/Plugins/{PluginName}/
├── {PluginName}Plugin.php      # 插件主类
├── Http/Middleware/             # 插件专用中间件
├── Widgets/                     # 插件专用小组件
├── Forms/                       # 插件专用表单组件
├── Tables/                      # 插件专用表格组件
└── Support/                     # 插件专用支持类
```

**插件生命周期：**
```php
<?php

namespace App\Infrastructure\Filament\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;

class CustomSearchPlugin implements Plugin
{
    public function register(Panel $panel): void
    {
        $panel
            ->plugins([
                // 插件通过面板注册
            ]);
    }
    
    public function boot(Panel $panel): void
    {
        // 注册全局配置、中间件、命令等
        $panel->middleware([
            CustomSearchMiddleware::class,
        ]);
    }
    
    public static function make(): static
    {
        return app(static::class);
    }
}
```

---

## 二、代码规范细则

### 2.1 PSR-12 与 Laravel 特有规范

| 规则 | 标准 | 示例 |
|------|------|------|
| 类名 | PascalCase | `OrderResource`, `OrderService` |
| 方法名 | camelCase | `createOrder()`, `getProducts()` |
| 属性名 | camelCase | `$orderId`, `$customerName` |
| 常量 | UPPER_SNAKE_CASE | `MAX_PAGE_SIZE`, `DEFAULT_PER_PAGE` |
| 文件名 | PascalCase.php | `OrderResource.php`, `LoginData.php` |
| 表名 | snake_case | `orders`, `order_items` |
| 字段名 | snake_case | `order_number`, `created_at` |

### 2.2 Filament 资源类命名规则

```
# 基本格式
app/Infrastructure/Filament/Resources/[业务域]/[实体名]Resource/

# 示例
app/Infrastructure/Filament/Resources/User/AdminResource/
├── AdminResource.php          # Resource 类
├── Pages/
│   ├── ListAdmins.php         # 列表页
│   ├── EditAdmin.php          # 编辑页
│   ├── CreateAdmin.php        # 创建页
│   └── ViewAdmin.php          # 详情页（可选）
├── Forms/
│   └── AdminForm.php          # 表单组件
├── Tables/
│   └── AdminTable.php         # 表格组件
└── RelationManagers/
    └── OrderRelationManager.php
```

**命名规则总结：**

| 文件 | 命名 | 示例 |
|------|------|------|
| Resource 类 | `[实体]Resource` | `AdminResource.php` |
| 列表页 | `List[实体]s` | `ListAdmins.php` |
| 创建页 | `Create[实体]` | `CreateAdmin.php` |
| 编辑页 | `Edit[实体]` | `EditAdmin.php` |
| 详情页 | `View[实体]` | `ViewAdmin.php` |
| 表单 | `[实体]Form` | `AdminForm.php` |
| 表格 | `[实体]Table` | `AdminTable.php` |
| 关系管理器 | `[实体]RelationManager` | `OrderRelationManager.php` |

### 2.3 表单字段统一配置

**2.3.1 字段类型映射表**

| 数据类型 | 字段类型 | 必填 | 唯一 | 示例 |
|---------|---------|------|------|------|
| 字符串 | `TextInput` | ✓ | ✓ | `->required()->unique(ignoreRecord: true)` |
| 文本/多行 | `Textarea` | | | `->maxLength(2000)` |
| 邮箱 | `TextInput` + `email()` | | ✓ | `->email()->unique(ignoreRecord: true)` |
| 手机号 | `TextInput` + `tel()` | | | `->tel()->regex('/^1[3-9]\d{9}$/'` |
| 数字 | `TextInput` + `numeric()` | | | `->numeric()->minValue(0)` |
| 价格 | `TextInput` + `money('CNY')` | | | `->money('CNY', decimalPlaces: 2)` |
| 整数 | `IntegerInput` | | | `->minValue(1)` |
| 选择 | `Select` | ✓ | | `->options([...])->required()` |
| 单选 | `RadioGroup` | | | `->options(Status::class)` |
| 多选 | `Select` + `multiple()` | | | `->multiple()->options([...])` |
| 布尔 | `Toggle` | | | `->label('启用')` |
| 日期 | `DatePicker` | | | `->native(false)` |
| 时间 | `TimePicker` | | | `->native(false)` |
| 日期时间 | `DateTimePicker` | | | `->native(false)` |
| 富文本 | `RichEditor` | | | `->toolbarItems([...])->columnSpanFull()` |
| 文件 | `FileUpload` | | | `->directory('uploads')->maxSize(5120)` |
| 图片 | `FileUpload` | | | `->image()->imageResizeMode('cover')` |
| 关联 | `Select` + `relationship()` | ✓ | | `->relationship('category', 'name')` |
| 多关联 | `Select` + `searchable()` | | | `->searchable()->multiple()->relationship(...)` |

**2.3.2 统一表单构建器模式**

```php
// ✅ 推荐：按逻辑分组的表单
public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('基本信息')
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->tel()
                    ->regex('/^1[3-9]\d{9}$/'),
            ]),
        Section::make('状态设置')
            ->schema([
                Select::make('status')
                    ->options(Status::class)
                    ->default(Status::ACTIVE->value)
                    ->required(),
                Toggle::make('enabled')
                    ->default(true),
            ]),
        Section::make('备注信息')
            ->schema([
                Textarea::make('notes')
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]),
    ]);
}
```

### 2.4 表格列统一配置

```php
// ✅ 推荐的表格配置
public static function table(Table $table): Table
{
    return $table
        // 分页与排序
        ->defaultPaginatedRecordLimit(20)
        ->defaultSort('created_at', 'desc')
        
        // 批量操作
        ->bulkActions([
            TableBulkAction::make('delete')
                ->action('bulkDelete')
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->destructive(),
            TableBulkAction::make('export')
                ->action('bulkExport'),
        ])
        ->actions([
            TableAction::make('edit')
                ->icon('heroicon-o-pencil')
                ->url(fn ($record) => self::getUrl('edit', [$record])),
            TableAction::make('view')
                ->icon('heroicon-o-eye'),
            TableAction::make('delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation(),
        ])
        // 列定义
        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->weight('medium'),
            TextColumn::make('email')
                ->searchable()
                ->copyable()
                ->copyMessage('邮箱已复制')
                ->copyMessageDuration(1500),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    Status::ACTIVE->value => 'success',
                    Status::INACTIVE->value => 'danger',
                    default => 'gray',
                }),
            TextColumn::make('created_at')
                ->dateTime('Y-m-d H:i')
                ->sortable()
                ->toggleable(),
        ])
        // 筛选
        ->filters([
            SelectFilter::make('status')
                ->options(Status::class),
            \Filament\Tables\Filters\TrashedFilter::make(),
        ])
        // 搜索
        ->search([
            'name',
            'email',
            'phone',
        ]);
}
```

### 2.5 权限控制标准化

**2.5.1 资源级权限配置**

```php
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',       // 查看权限
            'view_any',   // 列表权限
            'create',     // 创建权限
            'update',     // 更新权限
            'delete',     // 删除权限
            'delete_any', // 批量删除权限
            'restore',    // 恢复权限
            'force_delete', // 强制删除权限
            'reorder',    // 排序权限
            'view_any',   // 查看任意
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $user = auth()->user();
                if ($user->hasRole('admin')) {
                    return $query->whereNotNull('id');
                }
                return $query->where('created_by', $user->id);
            });
    }
}
```

**2.5.2 Policy 标准实现**

```php
<?php

namespace App\Domains\Product\Policies;

use App\Domains\User\Models\Admin;
use App\Domains\Product\Models\Product;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * 判断用户是否可以查看产品列表
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view-any::products');
    }
    
    /**
     * 判断用户是否可以查看单个产品
     */
    public function view(Admin $admin, Product $product): bool
    {
        return $admin->can('view::products');
    }
    
    /**
     * 判断用户是否可以创建产品
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create::products');
    }
    
    /**
     * 判断用户是否可以更新产品
     */
    public function update(Admin $admin, Product $product): bool
    {
        return $admin->can('update::products');
    }
    
    /**
     * 判断用户是否可以删除产品
     */
    public function delete(Admin $admin, Product $product): bool
    {
        return $admin->can('delete::products');
    }
}
```

**2.5.3 权限命名规范**

| 操作 | 权限命名 | 示例 |
|------|---------|------|
| 查看列表 | `view-any::{domain}` | `view-any::products` |
| 查看单个 | `view::{domain}` | `view::products` |
| 创建 | `create::{domain}` | `create::products` |
| 更新 | `update::{domain}` | `update::products` |
| 删除 | `delete::{domain}` | `delete::products` |
| 批量删除 | `delete-any::{domain}` | `delete-any::products` |
| 恢复 | `restore::{domain}` | `restore::products` |

### 2.6 枚举使用规范

```php
<?php

namespace App\Domains\Order\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待付款',
            self::PAID => '已付款',
            self::PROCESSING => '处理中',
            self::SHIPPED => '已发货',
            self::DELIVERED => '已送达',
            self::COMPLETED => '已完成',
            self::CANCELLED => '已取消',
            self::REFUNDED => '已退款',
        };
    }
    
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'primary',
            self::DELIVERED => 'success',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'gray',
        };
    }
}
```

**Filament 中使用：**
```php
// 下拉选择
Select::make('status')
    ->options(OrderStatus::class)
    ->default(OrderStatus::PENDING)
    ->required();

// 单选按钮
RadioGroup::make('status')
    ->options(OrderStatus::class)
    ->default(OrderStatus::PENDING)
    ->required();

// 表格列色标
TextColumn::make('status')
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        OrderStatus::PENDING->value => 'warning',
        OrderStatus::COMPLETED->value => 'success',
        default => 'gray',
    })
    ->formatStateUsing(fn (string $state): string => OrderStatus::from($state)->label());
```

---

## 三、代码组织方式

### 3.1 按业务域划分模块

```
app/Infrastructure/Filament/Resources/
├── User/                       # 用户域
│   ├── AdminResource.php
│   ├── CustomerResource.php
│   └── RoleResource.php
├── Product/                    # 商品域
│   ├── CategoryResource.php
│   ├── ProductResource.php
│   ├── BrandResource.php
│   └── AttributeResource.php
├── Trade/                      # 交易域
│   ├── OrderResource.php
│   ├── CartResource.php
│   └── PaymentResource.php
├── O2O/                        # 预约域
│   ├── StoreResource.php
│   ├── AppointmentResource.php
│   └── ServiceResource.php
└── Distribution/               # 分销域
    ├── DistributorResource.php
    ├── CommissionResource.php
    └── RelationshipResource.php
```

### 3.2 公共组件提取规则

**规则：当某个组件/表单字段/表格列在 3 个及以上资源中复用时，必须提取为独立组件。**

```php
// ✅ 正确：提取为公共组件
// app/Infrastructure/Filament/Components/Tenant/StatusToggle.php
namespace App\Infrastructure\Filament\Components;

use Filament\Forms\Components\Component;

class StatusToggle extends Component
{
    public static function make(string $name = 'enabled'): Component
    {
        return static::configure(function (Toggle $component) use ($name) {
            return $component
                ->name($name)
                ->default(true)
                ->live()
                ->label('状态');
        });
    }
}

// 在多个 Resource 中复用
// ProductResource.php
TextInput::make('name')->required(),
StatusToggle::make('enabled'),  // 复用

// OrderResource.php
TextInput::make('order_number')->required(),
StatusToggle::make('enabled'),  // 复用
```

**必须提取为公共组件的场景：**

| 场景 | 示例 | 最低复用次数 |
|------|------|------------|
| 状态切换 | `StatusToggle` | 3+ Resource |
| 关联选择 | `RelationshipSelect` | 3+ Resource |
| 金额输入 | `MoneyInput` | 3+ Resource |
| 日期范围 | `DateRangePicker` | 3+ Page |
| 搜索框 | `GlobalSearchBox` | 3+ Page |
| 图片上传 | `ImageUploader` | 3+ Form |
| 富文本编辑器 | `RichEditor` | 3+ Form |

### 3.3 Trait 使用规范

**适合通过 Trait 复用的功能：**

| Trait 名称 | 功能 | 适用场景 |
|-----------|------|---------|
| `HasTenantId` | 自动填充租户ID | 所有多租户模型 |
| `HasSoftDeletes` | 软删除支持 | 核心业务模型 |
| `HasUuid` | UUID 主键 | 公开 API 使用的模型 |
| `HasCustomFields` | 自定义字段 JSONB | CRM/灵活数据结构 |
| `HasSearchable` | 搜索作用域 | 需要全文搜索的模型 |
| `HasTimestamps` | 时间戳 | 需要创建/更新时间 |
| `HasOwner` | 数据归属 | 用户级数据隔离 |

**Trait 定义规范：**
```php
<?php

namespace App\Infrastructure\Support\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasOwner
{
    /**
     * 引导 trait：设置创建时自动填充 owner_id
     */
    protected static function bootHasOwner(): void
    {
        static::creating(function (Model $model) {
            if (!$model->owner_id && auth()->check()) {
                $model->owner_id = auth()->id();
            }
        });
    }
    
    /**
     * 作用域：按所有者过滤
     */
    public function scopeOwnedBy(Builder $query, int $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }
    
    /**
     * 作用域：非管理员只看到自己的数据
     */
    public function scopeForCurrentAdmin(Builder $query): Builder
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('super_admin')) {
            return $query->where('owner_id', $user->id);
        }
        return $query;
    }
}
```

**禁止使用的场景：**
- 不要使用 Trait 隐藏业务逻辑
- 不要创建超过 50 行的复杂 Trait
- 不要滥用 Trait（一个类最多使用 3 个 Trait）

---

## 四、Filament 页面开发规范

### 4.1 页面基类

```php
<?php

namespace App\Infrastructure\Filament\Resources\OrderResource\Pages;

use App\Infrastructure\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    // 禁止在此编写业务逻辑
    // 只保留展示相关配置
}
```

### 4.2 页面权限控制

```php
class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->hidden(fn () => !in_array($this->record->status, ['pending', 'cancelled'])),
        ];
    }
    
    // 数据级权限：只能编辑自己的订单
    protected function handleRecordUpdate($record, array $data): Model
    {
        $this->authorize('update', $record);
        return $record->update($data);
    }
}
```

### 4.3 小组件规范

```php
<?php

namespace App\Infrastructure\Filament\Widgets;

use App\Domains\Order\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('今日订单', Order::today()->count())
                ->description('较昨日')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('今日销售额', Order::today()->sum('total_amount'))
                ->description('元')
                ->color('primary'),
            Stat::make('待处理订单', Order::pending()->count())
                ->description('需要处理')
                ->color('warning'),
        ];
    }
}
```

---

## 五、DTO 规范

### 5.1 DTO 定义

```php
<?php

namespace App\Domains\Order\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

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
                fn ($item) => OrderItemData::fromArray($item),
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

### 5.2 Service 中使用 DTO

```php
class OrderService
{
    public function create(OrderCreateData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data->toArray());
            
            foreach ($data->items as $itemData) {
                $order->items()->create([
                    'product_id' => $itemData->productId,
                    'quantity' => $itemData->quantity,
                    'unit_price' => $itemData->price,
                ]);
            }
            
            $this->stockService->deduct($data->items);
            
            return $order;
        });
    }
}
```

---

## 六、数据库规范

### 6.1 表设计规范

| 规则 | 说明 | 示例 |
|------|------|------|
| 主键 | BIGINT UNSIGNED AUTO_INCREMENT | `$table->id()` |
| 外键 | 使用 `constrained()` | `$table->foreignId('user_id')->constrained()->cascadeOnDelete()` |
| 软删除 | 核心业务表必须开启 | `SoftDeletes` |
| 金额字段 | DECIMAL(10, 2)，严禁 FLOAT/DOUBLE | `->decimal('amount', 10, 2)` |
| 状态字段 | 使用 ENUM 或独立状态表 | `->string('status')->default('pending')` |
| JSON 字段 | 需要查询的 JSONB 创建索引 | `->json('custom_fields')->nullable()` |
| 时间戳 | 统一使用 `timestamps()` | `timestamps()` |
| UUID | 公开 API 标识使用 UUID | `->uuid()->unique()` |

### 6.2 索引规范

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->string('order_number')->unique();          // 唯一索引
    $table->string('status')->default('pending');
    $table->decimal('total_amount', 10, 2);
    $table->json('custom_fields')->nullable();          // JSON 字段
    $table->timestamps();
    $table->softDeletes();
    
    // 复合索引（覆盖常见查询）
    $table->index(['customer_id', 'status']);
    $table->index(['created_at']);
    
    // JSONB GIN 索引（MySQL 5.7+）
    $table->index(['custom_fields'], null, 'btree', 'AS (json_unquote(json_extract(`custom_fields`, '$.key'))')));
});
```

---

## 七、测试规范

### 7.1 Filament 测试模板

```php
<?php

// tests/Feature/Filament/Resources/OrderResourceTest.php

use App\Domains\Order\Models\Order;
use App\Domains\User\Models\Admin;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('admin can list orders', function () {
    $admin = Admin::factory()->create();
    $orders = Order::factory()->count(5)->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\OrderResource\Pages\ListOrders::class)
        ->loginAs($admin)
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(5);
});

test('admin can create order', function () {
    $admin = Admin::factory()->create();
    $customer = Customer::factory()->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\OrderResource\Pages\CreateOrder::class)
        ->loginAs($admin)
        ->fillForm([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-001',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    expect(Order::where('order_number', 'ORD-001'))->exists();
});

test('admin can delete order', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->loginAs($admin)
        ->callAction(DeleteAction::class)
        ->assertHasNoActionErrors();
    
    expect($order->fresh())->toBeNull();
});
```

---

*本文档为 `laravel-filament` 项目的核心开发规范，所有开发活动须遵循此规范。*
