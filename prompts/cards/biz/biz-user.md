# 用户域业务卡片

> **卡片 ID**: `biz-user`
> **优先级**: L2
> **依赖**: `base-domain`

---

## 用户域核心实体

### Admin (管理员)
- 表名: `admins` (或 `users` with guard='admin')
- Guard: `admin`
- 多态: 使用 spatie/laravel-permission

### Customer (前台用户)
- 表名: `customers`
- Guard: `customer`

### Role (角色)
- 表名: `roles` (spatie 包)

### Permission (权限)
- 表名: `permissions` (spatie 包)

## 关键字段

```yaml
fields:
  - name: name
    type: text
    required: true
    label: 姓名
  - name: email
    type: text
    required: true
    unique: true
    label: 邮箱
  - name: password
    type: text
    required: true
    label: 密码
    visibleOn: 'create'
  - name: phone
    type: text
    label: 手机号
  - name: status
    type: select
    enum: AdminStatus
    required: true
```

## 权限体系

使用 `spatie/laravel-permission`:
- 超级管理员: `super_admin` - 所有权限
- 运营: `operator` - 商品/订单管理
- 客服: `support` - 订单查看/退款
- 财务: `finance` - 账单/对账

## Filament 资源

| Resource | 说明 |
|----------|------|
| `AdminResource` | 管理员 CRUD + 角色分配 |
| `RoleResource` | 角色管理 + 权限分配 |
