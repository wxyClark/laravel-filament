# 项目文件结构优化方案

> **文档编号**: DEV-FILE-001
> **创建日期**: 2026-07-18
> **作者**: MiMo Code Agent
> **状态**: 待确认

---

## 当前结构分析

### 现有目录结构

```
app/
├── Console/Commands/          # Artisan 命令
├── Filament/
│   ├── Admin/Pages/           # 后台页面
│   └── Public/Resources/      # 前台资源
├── Http/
│   ├── Controllers/           # 控制器
│   │   ├── Api/               # API 控制器
│   │   └── Auth/              # 认证控制器
│   └── Middleware/             # 中间件
├── Infrastructure/
│   └── Filament/Resources/    # Filament 资源
├── Models/                    # 模型
├── Providers/                 # 服务提供者
└── Services/                  # 服务类
```

### 存在的问题

| 问题 | 说明 |
|------|------|
| DDD 分层不完整 | 缺少 Domain 层 |
| Services 位置不当 | 应在 Domain 层 |
| Infrastructure 过于简单 | 应包含更多基础设施组件 |
| 缺少 DTO | 没有值对象 |
| 缺少 Repository | 没有仓储模式 |
| 缺少 Event/Listener | 没有事件驱动 |

---

## 优化方案

### 目标结构

```
app/
├── Console/
│   └── Commands/              # Artisan 命令
│
├── Domains/                   # 领域层（核心业务）
│   ├── Address/               # 地址模块
│   │   ├── Models/            # Eloquent 模型
│   │   ├── Enums/             # 状态枚举
│   │   ├── Services/          # 领域服务
│   │   ├── Data/              # DTO 值对象
│   │   ├── Events/            # 领域事件
│   │   ├── Repositories/      # 仓储接口
│   │   └── Policies/          # 权限策略
│   │
│   ├── User/                  # 用户模块
│   │   ├── Models/
│   │   ├── Services/
│   │   └── ...
│   │
│   └── System/                # 系统模块
│       ├── Models/
│       ├── Services/
│       └── ...
│
├── Infrastructure/            # 基础设施层（框架适配）
│   ├── Filament/              # Filament 后台
│   │   ├── Resources/         # CRUD 资源
│   │   │   ├── Address/
│   │   │   ├── User/
│   │   │   └── System/
│   │   ├── Widgets/           # 小组件
│   │   └── Pages/             # 自定义页面
│   │
│   ├── Repositories/          # 仓储实现
│   │   └── Eloquent/
│   │       ├── AddressRepository.php
│   │       ├── UserRepository.php
│   │       └── ...
│   │
│   ├── Support/               # 支撑组件
│   │   ├── Traits/            # 公共 Trait
│   │   ├── Helpers/           # 辅助函数
│   │   └── Exceptions/        # 异常类
│   │
│   └── Cache/                 # 缓存服务
│       └── SettingCache.php
│
├── Http/                      # 接入层（请求/响应）
│   ├── Controllers/           # 控制器
│   │   └── Api/               # API 控制器
│   │       ├── AddressController.php
│   │       ├── UserController.php
│   │       └── ...
│   │
│   ├── Requests/              # 表单请求
│   │   ├── Address/
│   │   │   ├── StoreAddressRequest.php
│   │   │   └── UpdateAddressRequest.php
│   │   └── User/
│   │
│   ├── Resources/             # API 资源
│   │   ├── AddressResource.php
│   │   ├── UserResource.php
│   │   └── ...
│   │
│   └── Middleware/             # 中间件
│
├── Models/                    # 跨域共享模型
│   └── Setting.php            # 系统设置模型
│
├── Services/                  # 共享基础设施服务
│   ├── FileService.php        # 文件服务
│   ├── NotificationService.php # 通知服务
│   └── ExportService.php      # 导出服务
│
└── Providers/                 # 服务提供者
    ├── AppServiceProvider.php
    └── Filament/
        └── AdminPanelProvider.php
```

---

## 迁移步骤

### Step 1: 创建 Domain 目录结构

```bash
# 创建 Domain 目录
mkdir -p app/Domains/Address/{Models,Enums,Services,Data,Events,Repositories,Policies}
mkdir -p app/Domains/User/{Models,Services,Data,Events,Repositories,Policies}
mkdir -p app/Domains/System/{Models,Services,Data,Events,Repositories,Policies}
```

### Step 2: 迁移 Models

```bash
# 地址模块
mv app/Models/Address.php app/Domains/Address/Models/Address.php

# 用户模块
mv app/Models/Customer.php app/Domains/User/Models/Customer.php
mv app/Models/Admin.php app/Domains/User/Models/Admin.php
```

### Step 3: 更新命名空间

```php
// 原
namespace App\Models;

// 新
namespace App\Domains\Address\Models;
```

### Step 4: 创建 Repository 接口

```php
<?php

declare(strict_types=1);

namespace App\Domains\Address\Repositories;

use App\Domains\Address\Models\Address;

interface AddressRepositoryInterface
{
    public function find(int $id): ?Address;
    public function create(array $data): Address;
    public function update(Address $address, array $data): Address;
    public function delete(Address $address): bool;
    public function query(): \Illuminate\Database\Eloquent\Builder;
}
```

### Step 5: 创建 Repository 实现

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domains\Address\Models\Address;
use App\Domains\Address\Repositories\AddressRepositoryInterface;

class AddressRepository implements AddressRepositoryInterface
{
    public function __construct(
        private readonly Address $model
    ) {}
    
    public function find(int $id): ?Address
    {
        return $this->model->find($id);
    }
    
    public function create(array $data): Address
    {
        return $this->model->create($data);
    }
    
    public function update(Address $address, array $data): Address
    {
        $address->update($data);
        return $address->fresh();
    }
    
    public function delete(Address $address): bool
    {
        return $address->delete();
    }
    
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query();
    }
}
```

### Step 6: 创建 Service

```php
<?php

declare(strict_types=1);

namespace App\Domains\Address\Services;

use App\Domains\Address\Data\AddressCreateData;
use App\Domains\Address\Models\Address;
use App\Domains\Address\Repositories\AddressRepositoryInterface;

final class AddressService
{
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository
    ) {}
    
    public function create(AddressCreateData $data): Address
    {
        return $this->addressRepository->create($data->toArray());
    }
    
    public function update(Address $address, array $data): Address
    {
        return $this->addressRepository->update($address, $data);
    }
    
    public function delete(Address $address): bool
    {
        return $this->addressRepository->delete($address);
    }
}
```

### Step 7: 创建 DTO

```php
<?php

declare(strict_types=1);

namespace App\Domains\Address\Data;

use Illuminate\Contracts\Support\Arrayable;

readonly class AddressCreateData implements Arrayable
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $phone,
        public string $province,
        public string $city,
        public string $district,
        public string $address,
        public bool $isDefault = false,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            name: $data['name'],
            phone: $data['phone'],
            province: $data['province'],
            city: $data['city'],
            district: $data['district'],
            address: $data['address'],
            isDefault: $data['is_default'] ?? false,
        );
    }
    
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'phone' => $this->phone,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'is_default' => $this->isDefault,
        ];
    }
}
```

### Step 8: 绑定 Repository

```php
// AppServiceProvider.php
use App\Domains\Address\Repositories\AddressRepositoryInterface;
use App\Infrastructure\Repositories\Eloquent\AddressRepository;

public function register(): void
{
    $this->app->bind(
        AddressRepositoryInterface::class,
        AddressRepository::class
    );
}
```

### Step 9: 更新 Filament Resource

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Filament\Resources\Address;

use App\Domains\Address\Models\Address;
use App\Domains\Address\Services\AddressService;
use App\Domains\Address\Data\AddressCreateData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = '系统管理';
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('基本信息')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->required()
                        ->maxLength(20),
                ]),
            Forms\Components\Section::make('地址信息')
                ->schema([
                    Forms\Components\TextInput::make('province')
                        ->required(),
                    Forms\Components\TextInput::make('city')
                        ->required(),
                    Forms\Components\TextInput::make('district')
                        ->required(),
                    Forms\Components\TextInput::make('address')
                        ->required(),
                ]),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('province'),
                Tables\Columns\TextColumn::make('city'),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
```

### Step 10: 更新测试

```php
<?php

// tests/Unit/Services/AddressServiceTest.php

use App\Domains\Address\Services\AddressService;
use App\Domains\Address\Data\AddressCreateData;
use App\Domains\User\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AddressService::class);
});

test('create address with valid data', function () {
    $user = User::factory()->create();
    
    $data = new AddressCreateData(
        userId: $user->id,
        name: '张三',
        phone: '13800138000',
        province: '北京市',
        city: '北京市',
        district: '朝阳区',
        address: '三里屯太古里',
    );
    
    $address = $this->service->create($data);
    
    expect($address)->toBeInstanceOf(Address::class)
        ->and($address->name)->toBe('张三')
        ->and($address->user_id)->toBe($user->id);
});
```

---

## 验证清单

- [ ] 所有模型命名空间已更新
- [ ] 所有 Service 已创建
- [ ] 所有 Repository 已创建
- [ ] 所有 DTO 已创建
- [ ] 所有测试通过
- [ ] Pint 检查通过
- [ ] PHPStan 分析通过
- [ ] Filament 资源正常工作
- [ ] API 接口正常工作

---

## 总结

### 优化前后对比

| 维度 | 优化前 | 优化后 |
|------|--------|--------|
| DDD 分层 | 不完整 | 完整 |
| 业务逻辑 | 散落在各处 | 集中在 Domain |
| 测试覆盖 | 困难 | 容易 |
| 代码复用 | 困难 | 容易 |
| 维护成本 | 高 | 低 |

### 收益

1. **代码质量**: DDD 分层清晰，职责明确
2. **可测试性**: 依赖注入，易于测试
3. **可维护性**: 模块化设计，易于维护
4. **可扩展性**: 接口抽象，易于扩展
5. **团队协作**: 规范统一，协作高效
