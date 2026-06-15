# 地址信息管理模块

> 基于 Laravel 12 + Filament 3.x 实现的无限级地址管理功能模块

---

## 一、功能概述

### 1.1 核心功能

- **无限级分类**: 支持国家 → 省 → 市 → 区的无限级地址层级
- **级联筛选**: 上级选择自动过滤下级可选范围
- **CRUD 操作**: 完整的增删改查功能
- **公开 API**: 无需用户认证即可访问地址数据接口
- **数据缓存**: Redis 缓存优化查询性能

### 1.2 技术栈

- 后端: Laravel 12.x (PHP 8.2+)
- 前端: Filament 3.x (基于 Livewire)
- 数据库: MySQL 8.0+
- 缓存: Redis

---

## 二、数据模型设计

### 2.1 地址表结构

```
表名: addresses
```

| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT UNSIGNED | 主键 |
| parent_id | BIGINT UNSIGNED (nullable) | 上级地区 ID |
| name | VARCHAR(100) | 地区名称 |
| code | VARCHAR(60) UNIQUE | 行政区划代码 |
| level | VARCHAR(20) | 层级: country/province/city/district |
| level_num | INT | 层级深度: 1=国家, 2=省, 3=市, 4=区县 |
| pinyin | VARCHAR(100) | 拼音 |
| merge_path | JSON | 合并路径: ["国家","省","市"] |
| sort | INT | 排序 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |
| deleted_at | TIMESTAMP | 删除时间 |

### 2.2 关系定义

```
Address (parent_id) → Address
```

- 自关联关系，支持无限级嵌套
- 每个地址可以有多个子级，一个父级

### 2.3 索引优化

```php
$table->index('code');              // 按代码查询
$table->index('level');             // 按层级筛选
$table->index('level_num');         // 按层级排序
$table->index('parent_id');         // 按上级查询
$table->index(['level', 'parent_id']); // 复合索引
```

---

## 三、实现步骤

### 3.1 创建数据库迁移

```bash
php artisan make:migration create_addresses_table
```

**文件位置**: `database/migrations/2026_06_14_000001_create_addresses_table.php`

**运行迁移**:
```bash
php artisan migrate
```

### 3.2 创建数据模型

```bash
php artisan make:model Address
```

**文件位置**: `app/Models/Address.php`

**核心方法**:
- `parent()`: 获取上级地区
- `children()`: 获取下级地区
- `getFullPathAttribute()`: 获取完整路径

### 3.3 创建业务服务

```bash
php artisan make:service AddressService
```

**文件位置**: `app/Services/AddressService.php`

**核心功能**:
- `getAllAddresses()`: 获取所有地址
- `getChildrenByParentId()`: 按上级获取下级
- `getAddressTree()`: 获取树形结构
- `importAddresses()`: 导入行政区划数据

### 3.4 创建 API 控制器

```bash
php artisan make:controller Api/AddressApiController
```

**文件位置**: `app/Http/Controllers/Api/AddressApiController.php`

**公开接口** (无需认证):
- `GET /api/addresses` — 获取所有地址
- `GET /api/addresses/children?parent_id=X` — 获取子级
- `GET /api/addresses/by-level/{level}` — 按层级获取
- `GET /api/addresses/find/{code}` — 按代码查找
- `GET /api/addresses/tree` — 获取树形结构

**路由配置**: `routes/api-address.php`

**添加到 api.php**:
```php
require __DIR__ . '/api-address.php';
```

### 3.5 创建 Filament 资源

```bash
php artisan make:filament-resource Address --generate
```

**文件位置**: `app/Infrastructure/Filament/Resources/AddressResource.php`

**页面**:
- `Pages/ListAddresses.php` — 列表页
- `Pages/CreateAddress.php` — 创建页
- `Pages/EditAddress.php` — 编辑页

**组件**:
- `Forms/AddressForm.php` — 表单组件
- `Tables/AddressTable.php` — 表格组件

### 3.6 创建数据种子

```bash
php artisan make:seeder AddressSeeder
```

**文件位置**: `database/seeders/AddressSeeder.php`

**运行种子**:
```bash
php artisan db:seed --class=AddressSeeder
```

---

## 四、代码示例

### 4.1 模型示例

```php
// 创建地址
$province = Address::create([
    'parent_id' => null,
    'name' => '北京市',
    'code' => '110000',
    'level' => 'province',
    'level_num' => 2,
]);

// 获取下级
$children = $province->children;

// 获取完整路径
echo $address->full_path; // "中华人民共和国/北京市/北京市/朝阳区"
```

### 4.2 服务示例

```php
// 获取所有地址
$addresses = app(AddressService::class)->getAllAddresses();

// 获取子级
$children = app(AddressService::class)->getChildrenByParentId($parentId);

// 获取树形结构
$tree = app(AddressService::class)->getAddressTree();
```

### 4.3 API 示例

```bash
# 获取所有地址
curl http://localhost:8080/api/addresses

# 获取子级
curl "http://localhost:8080/api/addresses/children?parent_id=2"

# 获取树形结构
curl http://localhost:8080/api/addresses/tree
```

---

## 五、性能优化

### 5.1 缓存策略

- 使用 Redis 缓存地址数据
- 缓存 Key: `addresses.all`, `addresses.parent.{id}`, `addresses.level.{level}`
- 缓存 TTL: 默认 1 小时

### 5.2 数据库优化

- 创建复合索引优化查询
- 使用 JSON 字段存储合并路径
- 使用软删除保护历史数据

---

## 六、测试

### 6.1 运行测试

```bash
# 运行所有测试
php artisan test

# 运行指定测试
php artisan test tests/Unit/Models/AddressTest.php
php artisan test tests/Feature/AddressApiTest.php
```

### 6.2 测试覆盖

- 模型单元测试
- 服务层测试
- API 接口测试
