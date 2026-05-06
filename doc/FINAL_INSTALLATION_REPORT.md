# P0 工具链安装指南

## 📦 已安装的工具包
| 工具 | 版本 | 用途 |
|------|------|------|
| **PHPStan + Larastan** | 2.1.54 + 3.9.6 | 静态代码分析 |
| **Pest PHP** | 3.8.6 | 现代化测试框架 |
| **Laravel IDE Helper** | 3.7.0 | IDE 类型提示 |
| **Laravel Telescope** | 5.20.0 | 调试面板 |
| **Spatie Permission** | 6.25.0 | RBAC 权限控制 |

---

## 🔧 使用方法

## 🚀 常用命令

### 代码质量检查
```bash
# 静态分析
docker compose exec app ./vendor/bin/phpstan analyse

# 代码格式化
docker compose exec app ./vendor/bin/pint

# 运行所有测试
docker compose exec app ./vendor/bin/pest

# 运行特定测试
docker compose exec app ./vendor/bin/pest tests/Feature/ExampleTest.php

# 带覆盖率测试
docker compose exec app ./vendor/bin/pest --coverage
```

### IDE Helper
```bash
# 重新生成所有 Helper 文件
docker compose exec app php artisan ide-helper:generate
docker compose exec app php artisan ide-helper:models --write
docker compose exec app php artisan ide-helper:meta
```

### Telescope
```bash
# 启用 Telescope（在 .env 中设置）
TELESCOPE_ENABLED=true

# 清除旧数据
docker compose exec app php artisan telescope:clear

# 修剪数据（保留最近 24 小时）
docker compose exec app php artisan telescope:prune --hours=24
```

### Permission
```bash
# 创建角色
docker compose exec app php artisan tinker
>>> Spatie\Permission\Models\Role::create(['name' => 'admin']);

# 创建权限
>>> Spatie\Permission\Models\Permission::create(['name' => 'edit articles']);

# 分配权限给角色
>>> $role = Spatie\Permission\Models\Role::findByName('admin');
>>> $role->givePermissionTo('edit articles');
```

---

## 📍 访问地址
- **前台**: http://localhost:8082
- **Filament 后台**: http://localhost:8082/admin
- **Telescope 调试**: http://localhost:8082/telescope
