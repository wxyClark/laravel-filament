# 领域约束：RBAC 权限层级控制 (RBAC Permission Hierarchy)

> **版本**: v3.0 | **层級**: L2+ | **最后更新**: 2026-06-07

## 用途说明
实现多层级角色权限系统，支持继承、互斥和资源级权限。

## 适用场景
- 多租户系统的权限隔离
- 管理员/超级管理员/普通管理员分层
- 资源级权限（如只能管理自己创建的商品）

## 标准内容块
```markdown
## RBAC 权限约束

### 1. 角色层级
```
SuperAdmin (超级管理员)
  └── Admin (管理员)
        └── Editor (编辑)
```
- 高级角色自动继承低级角色权限
- 权限分配使用 Spatie `Permission` 包

### 2. 权限命名规范
```
{资源}.{操作}
例：products.create, products.edit, products.delete, products.export
```

### 3. 资源级权限 (Gates)
```php
Gate::define('update-product', function (Admin $admin, Product $product): bool {
    return $admin->id === $product->created_by;
});
```

### 4. Filament 集成
```php
public static function canView(Order $order): bool
{
    return auth()->user()->can('orders.view')
        || auth()->user()->can('own-orders.view') && $order->created_by === auth()->id();
}
```

### 约束
- 权限必须在 `AuthServiceProvider::boot()` 中注册
- 敏感操作必须在 Action 中校验 `Gate::authorize()`
- 角色创建/修改操作需记录审计日志
```
```
