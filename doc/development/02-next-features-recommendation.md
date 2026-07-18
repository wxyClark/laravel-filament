# 下一步功能开发推荐

> **文档编号**: DEV-NEXT-001
> **创建日期**: 2026-07-18
> **作者**: MiMo Code Agent
> **状态**: 待确认

---

## 当前项目状态分析

### 已有功能

| 模块 | 状态 | 说明 |
|------|------|------|
| 用户管理 | ✅ 基础 | Customer, Admin 模型 |
| 权限管理 | ✅ 基础 | Spatie Permission |
| 地址管理 | ✅ 完整 | AddressResource + API |
| 认证系统 | ✅ 基础 | Auth controllers |

### 缺失的通用功能

| 功能 | 优先级 | 必要性 |
|------|--------|--------|
| 系统设置 | P0 | 生产必须 |
| 活动日志 | P0 | 审计追踪 |
| 文件管理 | P1 | 媒体资源 |
| 通知系统 | P1 | 用户交互 |
| 数据仪表盘 | P1 | 运营分析 |
| 多租户 | P2 | SaaS 扩展 |
| 导入导出 | P1 | 数据处理 |
| 工作流引擎 | P2 | 业务流程 |

---

## 推荐功能 1: 系统设置 (System Settings)

### 功能描述

统一管理系统配置，支持动态配置、缓存、多环境管理。

### 核心能力

```
✅ 网站基本信息 (名称、Logo、联系方式)
✅ 邮件配置 (SMTP、队列)
✅ 缓存配置 (Redis、文件、数据库)
✅ 维护模式 (开启/关闭、白名单)
✅ 存储配置 (本地、S3、OSS)
✅ 安全设置 (登录限制、API 限流)
```

### 数据模型

```php
// Setting 模型
class Setting extends Model
{
    protected $fillable = [
        'group',      // 配置组 (site, mail, cache, etc.)
        'key',        // 配置键
        'value',      // 配置值
        'type',       // 类型 (string, integer, boolean, json)
        'description', // 描述
    ];
}
```

### Filament Resource

```php
class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = '系统管理';
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('group')
                ->options([
                    'site' => '网站设置',
                    'mail' => '邮件设置',
                    'cache' => '缓存设置',
                    'security' => '安全设置',
                ]),
            TextInput::make('key'),
            TextInput::make('value'),
            Select::make('type')
                ->options([
                    'string' => '字符串',
                    'integer' => '整数',
                    'boolean' => '布尔',
                    'json' => 'JSON',
                ]),
        ]);
    }
}
```

### 实现价值

- 运营人员可自行调整配置，无需开发介入
- 支持配置版本管理
- 配置变更自动清除缓存

---

## 推荐功能 2: 活动日志 (Activity Log)

### 功能描述

记录所有用户操作，支持审计追踪、操作回滚、行为分析。

### 核心能力

```
✅ 操作记录 (增删改查)
✅ 操作人追踪 (谁做了什么)
✅ 变更详情 (旧值 → 新值)
✅ IP 地址记录
✅ 操作回滚 (可选)
✅ 日志查询 (按时间、用户、操作类型)
```

### 技术方案

使用 `spatie/laravel-activitylog` 包：

```php
// 在 Model 中使用
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use LogsActivity;
    
    protected static $logAttributes = ['status', 'total_amount'];
    protected static $logOnlyDirty = true;
}
```

### Filament 集成

```php
class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = '系统管理';
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('causer.name')->label('操作人'),
                TextColumn::make('description')->label('操作描述'),
                TextColumn::make('event')->label('事件类型'),
                TextColumn::make('created_at')->label('时间'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
```

### 实现价值

- 满足合规审计要求
- 问题排查有据可查
- 支持操作回滚

---

## 推荐功能 3: 文件管理 (Media Library)

### 功能描述

统一管理文件上传，支持图片、文档、视频，支持多尺寸裁剪。

### 核心能力

```
✅ 多类型上传 (图片、文档、视频)
✅ 多尺寸裁剪 (缩略图、中图、原图)
✅ 文件分类 (按模块、按类型)
✅ 存储适配 (本地、S3、OSS)
✅ 文件预览
✅ 批量上传
```

### 技术方案

使用 `spatie/laravel-medialibrary` 包：

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->registerMediaConversions(function (MediaConversion $conversion) {
                $conversion->fit('cover', 300, 300)->nonQueued();
                $conversion->fit('cover', 800, 800)->nonQueued();
            });
    }
}
```

### Filament 集成

```php
use Filament\Forms\Components\FileUpload;

// 在 Resource form 中
FileUpload::make('images')
    ->multiple()
    ->image()
    ->imageResizeMode('cover')
    ->imageCropAspectRatio('1:1')
    ->imageResizeTargetWidth('300')
    ->imageResizeTargetHeight('300')
    ->maxSize(5120)
    ->collection('images'),
```

### 实现价值

- 统一文件管理，避免重复实现
- 支持 CDN 加速
- 节省存储空间

---

## 推荐功能 4: 通知系统 (Notification System)

### 功能描述

支持站内通知、邮件通知、短信通知，支持通知模板管理。

### 核心能力

```
✅ 站内通知 (收件箱)
✅ 邮件通知
✅ 短信通知 (可选)
✅ 通知模板管理
✅ 通知已读/未读
✅ 批量通知
✅ 通知偏好设置
```

### 数据模型

```php
// Notification 模型
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'data',
        'read_at',
    ];
    
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}
```

### Filament Resource

```php
class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = '系统管理';
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('read_at')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-bell')
                    ->falseIcon('heroicon-o-bell-alert'),
                TextColumn::make('title')->label('标题'),
                TextColumn::make('content')->label('内容')->limit(50),
                TextColumn::make('created_at')->label('时间'),
            ]);
    }
}
```

### 实现价值

- 提升用户体验
- 支持运营触达
- 满足合规要求

---

## 推荐功能 5: 数据仪表盘 (Dashboard Widgets)

### 功能描述

提供运营数据可视化，支持实时监控、趋势分析。

### 核心能力

```
✅ 关键指标卡片 (用户数、订单数、收入)
✅ 趋势图表 (折线图、柱状图)
✅ 数据表格 (最近订单、最新用户)
✅ 实时刷新
✅ 时间范围筛选
```

### Filament Widget

```php
class StatsOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.stats-overview';
    
    public function getStats(): array
    {
        return [
            Stat::make('用户总数', User::count())
                ->description('本月增长 ' . $this->getMonthlyGrowth('users'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('订单总数', Order::count())
                ->description('本月增长 ' . $this->getMonthlyGrowth('orders'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('总收入', '$' . number_format(Order::sum('total_amount'), 2))
                ->description('本月 $' . number_format($this->getMonthlyRevenue(), 2))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
```

### 实现价值

- 运营数据一目了然
- 支持数据驱动决策
- 提升管理效率

---

## 推荐功能 6: 导入导出 (Export/Import)

### 功能描述

支持 Excel/CSV 格式的数据导入导出，支持大文件处理。

### 核心能力

```
✅ Excel 导出
✅ CSV 导出
✅ Excel 导入
✅ CSV 导入
✅ 大文件分块处理
✅ 导入模板下载
✅ 导入进度追踪
```

### 技术方案

使用 `maatwebsite/excel` 包：

```php
// Export 类
class OrderExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Order::with('customer', 'items')->get();
    }
    
    public function headings(): array
    {
        return ['订单号', '客户', '金额', '状态', '创建时间'];
    }
    
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->customer->name,
            $order->total_amount,
            $order->status->label(),
            $order->created_at->format('Y-m-d H:i'),
        ];
    }
}
```

### Filament 集成

```php
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;

// 在 Resource 中
public function getActions(): array
{
    return [
        ExportAction::make()
            ->exporter(OrderExport::class),
        ImportAction::make()
            ->importer(OrderImporter::class),
    ];
}
```

### 实现价值

- 满足数据迁移需求
- 支持批量操作
- 提升工作效率

---

## 推荐功能 7: 高级搜索 (Advanced Search)

### 功能描述

支持全文搜索、高级过滤、搜索建议。

### 核心能力

```
✅ 全文搜索 (中文分词)
✅ 高级过滤 (多条件组合)
✅ 搜索建议 (自动补全)
✅ 搜索历史
✅ 搜索结果高亮
```

### 技术方案

使用 `Laravel Scout` + `Meilisearch`：

```php
// Model 中
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;
    
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->name,
        ];
    }
}
```

### Filament 集成

```php
// 在 Resource 中启用搜索
protected static ?string $recordTitleAttribute = 'name';

public static function getGlobalSearchResultTitle(Model $record): string
{
    return $record->name;
}

public static function getGlobalSearchResultDetails(Model $record): array
{
    return [
        '分类' => $record->category->name,
        '价格' => '$' . $record->price,
    ];
}
```

### 实现价值

- 提升搜索效率
- 支持复杂查询
- 改善用户体验

---

## 推荐功能 8: 多租户 (Multi-Tenancy)

### 功能描述

支持 SaaS 多租户架构，每个租户数据隔离。

### 核心能力

```
✅ 租户管理 (创建、配置、计费)
✅ 数据隔离 (每个租户独立数据库或 schema)
✅ 权限隔离 (租户间权限隔离)
✅ 资源配额 (存储、用户数、API 调用)
✅ 计费管理 (订阅、用量、账单)
```

### 技术方案

使用 `stancl/tenancy` 包：

```php
// Tenant 模型
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'domain',
        'plan',
        'status',
    ];
}
```

### Filament 集成

```php
// 在 AdminPanelProvider 中
->tenant(Tenant::class)
->tenantRegistration(CreateTenant::class)
->tenantMenu(fn (Tenant $tenant) => [
    // 租户特定菜单
])
```

### 实现价值

- 支持 SaaS 商业模式
- 数据安全隔离
- 支持多租户计费

---

## 优先级排序

### P0 (立即开发)

1. **系统设置** - 生产必须，配置集中管理
2. **活动日志** - 合规审计，问题排查

### P1 (近期开发)

3. **文件管理** - 媒体资源，提升体验
4. **通知系统** - 用户触达，运营支撑
5. **数据仪表盘** - 运营分析，决策支持
6. **导入导出** - 数据处理，效率提升

### P2 (后续开发)

7. **高级搜索** - 搜索体验，复杂查询
8. **多租户** - SaaS 扩展，商业模式

---

## 实现路线图

### Phase 1 (2周)

- 系统设置模块
- 活动日志模块

### Phase 2 (3周)

- 文件管理模块
- 通知系统模块

### Phase 3 (2周)

- 数据仪表盘
- 导入导出功能

### Phase 4 (3周)

- 高级搜索
- 多租户支持

---

## 开发建议

1. **渐进式开发**: 一个模块一个模块开发，确保质量
2. **TDD 驱动**: 每个功能先写测试
3. **文档先行**: 先写需求文档，再写代码
4. **代码复用**: 尽量使用现有包，避免重复造轮子
5. **性能优先**: 注意查询优化和缓存策略
