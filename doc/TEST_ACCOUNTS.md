# 测试账号信息

本文档提供前后端系统的测试账号和密码，用于开发和测试。

---

## 🔐 后台管理员账号 (Admin)

**访问地址**: http://localhost:8082/admin

| 字段 | 值 |
|------|-----|
| **邮箱** | `admin@example.com` |
| **密码** | `password123` |
| **姓名** | 系统管理员 |
| **角色** | 管理员 |

**用途**: 
- 访问 Filament 后台管理系统
- 管理资源、用户、订单等数据
- 配置系统设置

---

## 👤 前台用户账号 (Customer)

**访问地址**: 
- 登录: http://localhost:8082/login
- 注册: http://localhost:8082/register

| 字段 | 值 |
|------|-----|
| **邮箱** | `customer@example.com` |
| **密码** | `password123` |
| **姓名** | 测试用户 |
| **手机号** | `13800138000` |
| **角色** | 普通用户 |

**用途**:
- 访问前台用户界面
- 测试用户认证功能（登录/注册/登出）
- 测试用户相关业务流程

---

## 🧪 快速测试指南

### 1. 测试后台登录

```bash
# 访问后台登录页面
curl -c cookies.txt -X GET http://localhost:8082/admin/login

# 使用账号登录
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8082/admin/login \
  -d "email=admin@example.com&password=password123"
```

### 2. 测试前台登录

```bash
# 访问前台登录页面
curl -c cookies.txt -X GET http://localhost:8082/login

# 使用账号登录
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8082/login \
  -d "email=customer@example.com&password=password123"
```

### 3. 运行自动化测试

```bash
# 运行所有测试
docker compose exec app ./vendor/bin/pest

# 仅运行认证相关测试
docker compose exec app ./vendor/bin/pest tests/Feature/Auth/
```

---

## ⚠️ 重要提示

### Filament 后台登录注意事项

**Filament 登录页面依赖 JavaScript (Livewire)**

1. **必须启用 JavaScript**：Filament 使用 Livewire 处理表单提交，浏览器必须启用 JavaScript
2. **不要手动提交表单**：让 Livewire 自动处理，点击"登录"按钮即可
3. **常见错误**：
   - ❌ `MethodNotAllowedHttpException` - 通常是因为禁用了 JavaScript 或手动提交了表单
   - ✅ 解决方案：确保浏览器启用了 JavaScript，刷新页面后正常点击登录按钮

4. **清除浏览器缓存**：如果遇到问题，尝试清除浏览器缓存或使用无痕模式

### 通用注意事项

1. **生产环境**: 这些是测试账号，**切勿**在生产环境中使用
2. **密码安全**: 实际项目中应使用强密码策略
3. **数据隔离**: 测试数据会在每次运行迁移时重置
4. **账号创建**: 如需创建更多测试账号，可使用以下命令：

```bash
# 创建新的 Admin 账号
docker compose exec app php artisan tinker --execute="
\App\Models\Admin::create([
    'name' => '新管理员',
    'email' => 'newadmin@example.com',
    'password' => bcrypt('password123'),
]);"

# 创建新的 Customer 账号
docker compose exec app php artisan tinker --execute="
\App\Models\Customer::create([
    'name' => '新用户',
    'email' => 'newuser@example.com',
    'phone' => '13900139000',
    'password' => bcrypt('password123'),
]);"
```

---

## 🎨 UI 设计说明

### 前台登录页面
- **设计风格**: 现代渐变紫色背景 (#667eea → #764ba2)
- **特点**: 
  - 白色圆角卡片，带阴影效果
  - 滑入动画
  - 输入框聚焦高亮
  - 响应式设计

### 后台登录页面
- **设计风格**: Filament 原生风格，自定义品牌色
- **特点**:
  - 主色调: #667eea (与前台一致)
  - 简洁专业的后台界面
  - 保持 Filament 功能完整性

---

## 📝 更新日期

最后更新: 2026-05-07
