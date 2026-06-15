# 地址模块使用指南

> 如何在项目中使用地址管理功能

---

## 一、Filament 后台使用

### 1.1 访问地址管理

1. 登录 Filament 后台: `http://localhost:8080/admin`
2. 导航栏找到 "基础数据" 组
3. 点击 "地址管理" 菜单

### 1.2 添加地址

1. 点击 "新建地址" 按钮
2. 填写必填字段:
   - 上级地区（可选）
   - 地区名称（必填）
   - 行政区划代码（必填）
   - 层级（必填）
3. 点击 "保存"

### 1.3 编辑地址

1. 在列表中点击编辑按钮
2. 修改字段
3. 点击 "保存"

### 1.4 删除地址

1. 在列表中点击删除按钮
2. 确认删除

---

## 二、API 接口使用

### 2.1 获取所有地址

```bash
curl http://localhost:8080/api/addresses
```

**响应**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "北京市",
      "code": "110000",
      "level": "province",
      "parent_id": null,
      "merge_path": ["中华人民共和国", "北京市"]
    }
  ]
}
```

### 2.2 级联选择示例

```javascript
// 获取省级列表
fetch('/api/addresses/by-level/province')
  .then(res => res.json())
  .then(data => {
    // 渲染省级下拉框
  });

// 当选择省级时，获取市级列表
function onProvinceChange(provinceId) {
  fetch(`/api/addresses/children?parent_id=${provinceId}`)
    .then(res => res.json())
    .then(data => {
      // 渲染市级下拉框
    });
}
```

---

## 三、在业务中使用

### 3.1 在订单中使用地址

```php
// 在订单模型中
class Order extends Model
{
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }
}
```

### 3.2 在客户中使用地址

```php
class Customer extends Model
{
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
```

---

## 四、权限配置

### 4.1 创建权限

```php
// 在 Seeder 中
Permission::create(['name' => 'view::addresses']);
Permission::create(['name' => 'create::addresses']);
Permission::create(['name' => 'update::addresses']);
Permission::create(['name' => 'delete::addresses']);
```

### 4.2 分配角色

```php
$role = Role::findByName('admin');
$role->givePermissionTo([
    'view::addresses',
    'create::addresses',
    'update::addresses',
    'delete::addresses',
]);
```

---

## 五、常见问题

### Q1: 如何自定义层级？

修改 `Address` 模型中的 `level` 字段值和 `AddressForm` 中的选项。

### Q2: 如何禁用某些层级？

在 `AddressForm` 中移除对应选项，或修改 `AddressTable` 中的筛选器。

### Q3: 如何优化查询性能？

- 使用 Redis 缓存
- 添加数据库索引
- 使用 Eager Loading 避免 N+1 查询
