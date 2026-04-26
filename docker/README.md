# 🐳 Laravel Filament Docker 配置

本目录包含 Laravel Filament 应用的 Docker 配置文件。

## 📁 目录结构

```
docker/
├── php/
│   └── Dockerfile          # PHP 8.2-FPM 镜像定义
├── nginx/
│   └── default.conf        # Nginx 配置文件
└── README.md               # 本文档
```

## 🛠️ 技术栈

- **PHP**: 8.2-FPM
  - 扩展：Redis, AMQP, Bcmath, GD, OPcache
  - 优化：生产级 OPcache 配置
  
- **Nginx**: Alpine 版本
  - Laravel 伪静态规则
  - 静态资源缓存策略

## 🚀 使用方法

### 构建镜像

```bash
cd /home/clark/www/laravel-filament
docker compose build app
```

### 启动应用

```bash
docker compose up -d
```

### 进入容器

```bash
# 进入 PHP 容器
docker compose exec app bash

# 执行 Artisan 命令
docker compose exec app php artisan migrate
```

### 查看日志

```bash
# 查看应用日志
docker compose logs -f app

# 查看 Nginx 日志
docker compose logs -f nginx
```

## 🔗 依赖服务

本应用依赖以下**共享基础设施**服务：

| 服务 | 容器名 | 端口 | 说明 |
|------|--------|------|------|
| MySQL | infra-mysql | 3306 | 数据库服务 |
| Redis | infra-redis | 6379 | 缓存服务 |
| RabbitMQ | infra-rabbitmq | 5672 | 消息队列 |

### 启动基础设施

在使用本应用前，请确保基础设施已启动：

```bash
cd /home/clark/www/infrastructure
docker compose up -d
```

详细文档：[基础设施 README](../../infrastructure/README.md)

## ⚙️ 配置说明

### PHP Dockerfile

位置：`docker/php/Dockerfile`

特性：
- 基于 `php:8.2-fpm` 多阶段构建
- 预装 Laravel 所需扩展
- 启用 OPcache+JIT 生产优化
- PHP JIT 支持
- 常用 PHP 配置（时区、内存限制、文件上传）

### Nginx 配置

位置：`docker/nginx/default.conf`

特性：
- Laravel 路由重写规则
- 静态资源缓存（30天）
- PHP-FPM FastCGI 配置
- 安全响应头（X-Frame-Options, CSP 等）
- Gzip 压缩

## 📝 环境变量

在项目的 `.env` 文件中配置：

```env
# 数据库（指向基础设施）
DB_HOST=infra-mysql
DB_PORT=3306
DB_DATABASE=db_laravel-filament
DB_USERNAME=user_laravel-filament
DB_PASSWORD=<从基础设施获取>

# Redis（指向基础设施）
REDIS_HOST=infra-redis
REDIS_PORT=6379

# RabbitMQ（指向基础设施）
QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=infra-rabbitmq
RABBITMQ_PORT=5672
```

## 💾 数据备份

**重要**：数据库备份请在**基础设施层**执行，不在本项目中。

执行备份：
```bash
cd /home/clark/www/infrastructure
./backup.sh
```

详细文档：[基础设施备份指南](../../infrastructure/README.md#-数据备份)

## 🆘 常见问题

### Q: 无法连接到数据库？

A: 检查基础设施是否运行：
```bash
docker ps | grep infra-mysql
```

### Q: 如何重启应用？

A: 
```bash
docker compose restart
```

### Q: 如何更新 PHP 扩展？

A: 修改 `docker/php/Dockerfile`，然后重新构建：
```bash
docker compose build app
docker compose up -d
```

## 📚 相关文档

- [基础设施管理](../../infrastructure/README.md)
- [Portainer 操作指南](../../PORTAINER_STACKS_CREATION_GUIDE.md)
- [Docker 最佳实践](./BEST_PRACTICES.md)

---

**最后更新**: 2026-04-27  
**维护者**: Laravel Filament Team
