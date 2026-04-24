# 运维规范：部署检查清单

## 用途说明
确保每次部署前完成所有必要的检查，降低部署风险。

## 适用场景
- 生产环境部署
- 预发布环境发布
- 数据库迁移执行

## 标准内容块
```markdown
## 部署前检查清单

### 代码质量检查
- [ ] 所有测试通过 (`php artisan test`)
- [ ] 代码风格检查通过 (`./vendor/bin/pint --test`)
- [ ] 静态分析通过 (`./vendor/bin/phpstan analyse`)
- [ ] 没有 `dd()` 或 `dump()` 残留
- [ ] 没有硬编码的敏感信息

### 数据库检查
- [ ] 迁移文件已准备好 (`php artisan migrate --pretend`)
- [ ] 回滚脚本已测试 (`php artisan migrate:rollback`)
- [ ] 种子数据已准备（如需要）
- [ ] 数据库备份已完成

### 环境配置
- [ ] `.env` 文件已更新
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] 缓存已清理 (`php artisan config:clear`)

### 优化配置
```bash
# 配置缓存
php artisan config:cache

# 路由缓存
php artisan route:cache

# 视图缓存
php artisan view:cache

# 事件缓存
php artisan event:cache

# 清理过期缓存
php artisan cache:clear
```

### 队列与任务
- [ ] Horizon 已重启 (`php artisan horizon:terminate`)
- [ ] 计划任务已配置 (`php artisan schedule:run`)
- [ ] 失败任务已处理
- [ ] 队列连接正常

### 前端资源
- [ ] npm/pnpm 构建完成 (`npm run build`)
- [ ] 资源版本已更新
- [ ] CDN 缓存已刷新

### 安全检查
- [ ] HTTPS 已配置
- [ ] CORS 配置正确
- [ ] CSRF 保护已启用
- [ ] 敏感路由已保护

## 零停机部署脚本
```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 开始部署..."

# 进入维护模式
echo "进入维护模式..."
php artisan down --refresh=15 --retry=60

# 拉取最新代码
echo "拉取最新代码..."
git pull origin main

# 安装依赖
echo "安装依赖..."
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 数据库迁移
echo "执行数据库迁移..."
php artisan migrate --force

# 缓存优化
echo "优化缓存..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 重启服务
echo "重启队列服务..."
php artisan horizon:terminate

# 链接存储
echo "链接存储..."
php artisan storage:link

# 设置权限
echo "设置权限..."
chown -R www-data:www-data storage bootstrap/cache

# 退出维护模式
echo "退出维护模式..."
php artisan up

echo "✅ 部署完成！"
```

## 回滚脚本
```bash
#!/bin/bash
# rollback.sh

set -e

echo "⚠️ 开始回滚..."

# 回滚代码
git reset --hard HEAD~1

# 回滚数据库（如果需要）
# php artisan migrate:rollback

# 清理缓存
php artisan config:cache
php artisan route:cache

# 重启服务
php artisan horizon:terminate

echo "✅ 回滚完成！"
```

## 部署后验证
- [ ] 首页正常访问
- [ ] API 接口正常响应
- [ ] 队列任务正常执行
- [ ] 定时任务正常运行
- [ ] 错误监控正常
- [ ] 日志正常记录
```
```
