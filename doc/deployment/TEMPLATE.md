# 部署文档模板

> **文档编号**: DEP-{模块}-{序号}
> **创建日期**: YYYY-MM-DD
> **作者**: {姓名}
> **状态**: 草稿 | 评审中 | 已确认 | 已废弃
> **关联设计**: DES-{模块}-{序号}
> **关联开发**: DEV-{模块}-{序号}

---

## 1. 部署概述

### 1.1 部署目标
{描述部署要达成的目标}

### 1.2 部署范围
{描述部署的范围和边界}

### 1.3 部署环境
| 环境 | 用途 | 地址 |
|------|------|------|
| Local | 本地开发 | http://localhost:8082 |
| Staging | 测试环境 | {地址} |
| Production | 生产环境 | {地址} |

---

## 2. 环境要求

### 2.1 服务器要求

| 组件 | 最低要求 | 推荐配置 |
|------|----------|----------|
| CPU | 2 核 | 4 核 |
| 内存 | 4GB | 8GB |
| 磁盘 | 20GB | 50GB |
| 带宽 | 10Mbps | 50Mbps |

### 2.2 软件要求

| 软件 | 版本要求 | 说明 |
|------|----------|------|
| Docker | 24.0+ | 容器运行时 |
| Docker Compose | 2.20+ | 容器编排 |
| Nginx | 1.24+ | Web 服务器 |
| PHP | 8.5+ | 应用运行时 |
| MySQL | 8.0+ | 数据库 |
| Redis | 7.0+ | 缓存 |

---

## 3. 部署准备

### 3.1 代码准备

```bash
# 1. 拉取最新代码
git fetch origin
git checkout main
git pull origin main

# 2. 检查代码状态
git status
git log --oneline -5
```

### 3.2 环境配置

```bash
# 1. 复制环境配置文件
cp .env.example .env

# 2. 生成应用密钥
php artisan key:generate

# 3. 配置数据库连接
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD={密码}

# 4. 配置缓存
REDIS_HOST=redis
REDIS_PASSWORD={密码}
REDIS_PORT=6379
```

### 3.3 依赖安装

```bash
# 1. 安装 PHP 依赖
composer install --no-dev --optimize-autoloader

# 2. 安装 Node.js 依赖
npm ci

# 3. 构建前端资源
npm run build
```

---

## 4. 部署步骤

### 4.1 Docker 部署

```bash
# 1. 构建镜像
docker compose build

# 2. 启动容器
docker compose up -d

# 3. 检查容器状态
docker compose ps

# 4. 查看日志
docker compose logs -f
```

### 4.2 数据库初始化

```bash
# 1. 运行迁移
docker compose exec app php artisan migrate --force

# 2. 填充数据
docker compose exec app php artisan db:seed --force

# 3. 创建管理员
docker compose exec app php artisan make:filament-user
```

### 4.3 缓存配置

```bash
# 1. 清除缓存
docker compose exec app php artisan cache:clear

# 2. 配置缓存
docker compose exec app php artisan config:cache

# 3. 路由缓存
docker compose exec app php artisan route:cache

# 4. 视图缓存
docker compose exec app php artisan view:cache

# 5. 事件缓存
docker compose exec app php artisan event:cache
```

### 4.4 重启服务

```bash
# 1. 重启队列
docker compose exec app php artisan queue:restart

# 2. 重启 Horizon (如果使用)
docker compose exec app php artisan horizon:terminate

# 3. 重启容器
docker compose restart
```

---

## 5. 验证检查

### 5.1 功能验证

| 检查项 | 命令/方法 | 预期结果 | 状态 |
|--------|-----------|----------|------|
| 应用访问 | curl http://localhost:8082 | 200 OK | |
| Admin 面板 | 访问 /admin | 登录页面 | |
| API 接口 | curl /api/xxx | 正常响应 | |
| 数据库连接 | php artisan tinker | 连接成功 | |
| Redis 连接 | redis-cli ping | PONG | |

### 5.2 性能验证

| 指标 | 目标值 | 实际值 | 状态 |
|------|--------|--------|------|
| 响应时间 | < 200ms | | |
| TPS | > 100 | | |
| 内存使用 | < 256MB | | |
| CPU 使用 | < 50% | | |

### 5.3 安全验证

| 检查项 | 方法 | 预期结果 | 状态 |
|--------|------|----------|------|
| HTTPS | 访问 http:// | 重定向到 https | |
| 认证 | 未登录访问受保护页面 | 重定向到登录 | |
| 授权 | 越权访问 | 403 Forbidden | |
| 输入验证 | 提交恶意数据 | 被拦截 | |

---

## 6. 回滚方案

### 6.1 代码回滚

```bash
# 1. 回滚到指定版本
git revert HEAD

# 2. 重新部署
docker compose build
docker compose up -d
```

### 6.2 数据库回滚

```bash
# 1. 回滚迁移
docker compose exec app php artisan migrate:rollback --step=1

# 2. 检查数据状态
docker compose exec app php artisan tinker
```

### 6.3 完整回滚

```bash
# 1. 回滚代码
git revert HEAD

# 2. 回滚数据库
docker compose exec app php artisan migrate:rollback

# 3. 重新部署
docker compose build
docker compose up -d

# 4. 重新配置缓存
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

---

## 7. 监控告警

### 7.1 监控配置

| 监控项 | 工具 | 告警阈值 |
|--------|------|----------|
| 服务器状态 | Prometheus | CPU > 80%, 内存 > 80% |
| 应用日志 | Laravel Telescope | 错误 > 10/分钟 |
| 数据库 | MySQL Exporter | 连接数 > 80% |
| Redis | Redis Exporter | 内存 > 80% |

### 7.2 告警通知

| 告警级别 | 通知方式 | 响应时间 |
|----------|----------|----------|
| P0-紧急 | 电话 + 短信 | 5分钟 |
| P1-严重 | 钉钉/微信 | 15分钟 |
| P2-一般 | 邮件 | 1小时 |

---

## 8. 维护操作

### 8.1 日常维护

```bash
# 1. 查看日志
docker compose logs -f --tail=100

# 2. 检查磁盘空间
df -h

# 3. 清除过期缓存
docker compose exec app php artisan cache:clear

# 4. 优化数据库
docker compose exec app php artisan db:optimize
```

### 8.2 紧急处理

```bash
# 1. 重启容器
docker compose restart

# 2. 进入容器调试
docker compose exec app bash

# 3. 查看进程
docker compose exec app ps aux

# 4. 检查端口
netstat -tlnp
```

---

## 9. 部署记录

| 版本 | 日期 | 部署人 | 变更内容 | 状态 |
|------|------|--------|----------|------|
| v1.0 | YYYY-MM-DD | {姓名} | 初始部署 | 成功 |
| v1.1 | YYYY-MM-DD | {姓名} | {变更} | 成功 |

---

## 10. 签署

| 角色 | 姓名 | 日期 | 签名 |
|------|------|------|------|
| 运维人员 | | | |
| 开发负责人 | | | |
| 项目经理 | | | |

---

## 变更记录

| 版本 | 日期 | 作者 | 变更内容 |
|------|------|------|----------|
| 1.0 | YYYY-MM-DD | {姓名} | 初始版本 |
