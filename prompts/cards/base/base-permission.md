# 权限控制卡片

> **卡片 ID**: `base-permission`
> **优先级**: L0
> **依赖**: `base-naming`

---

## Resource 权限配置

```php
class {{entity}}Resource extends Resource
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',       // 查看单个
            'view_any',   // 查看列表
            'create',     // 创建
            'update',     // 更新
            'delete',     // 删除
            'delete_any', // 批量删除
            'restore',    // 恢复
            'force_delete', // 强制删除
        ];
    }
}
```

## 权限命名规范

| 操作 | 权限命名格式 | 示例 |
|------|------------|------|
| 查看列表 | `view-any::{domain}` | `view-any::orders` |
| 查看单个 | `view::{domain}` | `view::orders` |
| 创建 | `create::{domain}` | `create::orders` |
| 更新 | `update::{domain}` | `update::orders` |
| 删除 | `delete::{domain}` | `delete::orders` |
| 批量删除 | `delete-any::{domain}` | `delete-any::orders` |

## Policy 模板

```php
<?php

namespace App\Domains\{{domain}}\Policies;

use App\Domains\User\Models\Admin;
use {{model_path}};
use Illuminate\Auth\Access\Response;

class {{entity}}Policy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view-any::{{entity_snake}}');
    }
    
    public function view(Admin $admin, {{model_class}} ${{entity_snake}}): bool
    {
        return $admin->can('view::{{entity_snake}}');
    }
    
    public function create(Admin $admin): bool
    {
        return $admin->can('create::{{entity_snake}}');
    }
    
    public function update(Admin $admin, {{model_class}} ${{entity_snake}}): bool
    {
        return $admin->can('update::{{entity_snake}}');
    }
    
    public function delete(Admin $admin, {{model_class}} ${{entity_snake}}): bool
    {
        return $admin->can('delete::{{entity_snake}}');
    }
}
```

## Eloquent 查询数据隔离

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->when(!auth()->user()->hasRole('super_admin'), function ($query) {
            return $query->where('created_by', auth()->id());
        });
}
```
