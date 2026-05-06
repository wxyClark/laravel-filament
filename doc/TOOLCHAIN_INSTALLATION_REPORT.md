# 工具链安装指南

## 📦 已安装的工具包
| 工具 | 版本 | 用途 |
|------|------|------|
| **PHPStan + Larastan** | 2.1.54 + 3.9.6 | 静态代码分析 |
| **Pest PHP** | 3.8.6 | 现代化测试框架 |
| **Laravel IDE Helper** | 3.7.0 | IDE 类型提示 |
| **Laravel Telescope** | 5.20.0 | 调试面板 |
| **Spatie Permission** | 6.25.0 | RBAC 权限控制 |
| **Laravel Pint** | ^1.14 | 代码格式化 |

---

## 🔧 使用方法

### 运行数据库迁移
```bash
docker compose exec app php artisan migrate
```

这将创建：
- Telescope 数据表
- Permission 角色和权限表

### 生成 IDE Helper
```bash
docker compose exec app php artisan ide-helper:generate
docker compose exec app php artisan ide-helper:models --write
```

生成的文件：
- `_ide_helper.php` - Facade 和辅助函数提示
- `_ide_helper_models.php` - Eloquent 模型属性提示

### 配置 Telescope
在 `.env` 文件中添加：
```env
TELESCOPE_ENABLED=true
```
访问：`http://localhost:8082/telescope`

---

## 🚀 常用命令

### 代码质量检查
```bash
# 静态分析
docker compose exec app ./vendor/bin/phpstan analyse

# 代码格式化
docker compose exec app ./vendor/bin/pint

# 运行测试
docker compose exec app ./vendor/bin/pest
```

### IDE Helper
```bash
# 重新生成 Helper 文件
docker compose exec app php artisan ide-helper:generate
docker compose exec app php artisan ide-helper:models --write
docker compose exec app php artisan ide-helper:meta
```

### Telescope
```bash
# 清除旧数据
docker compose exec app php artisan telescope:clear

# 修剪数据（保留最近 24 小时）
docker compose exec app php artisan telescope:prune --hours=24
```

### Permission
```bash
# 发布配置（如需要重新发布）
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"

# 发布迁移
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=migrations
```

---

## 📍 访问地址
- **前台**: http://localhost:8082
- **Filament 后台**: http://localhost:8082/admin
- **Telescope 调试**: http://localhost:8082/telescope
