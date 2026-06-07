# Laravel + Filament Docker 环境

## 📋 环境概述

本 Docker 环境包含以下服务：

- **PHP 8.2-FPM**: Laravel 应用运行环境
- **Nginx**: Web 服务器
- **MySQL 8.0**: 数据库
- **Redis**: 缓存和队列
- **Mailpit**: 本地邮件测试

## 🚀 快速开始

### 1. 启动环境

```bash
# 启动所有服务
./docker.sh start

# 或使用 docker compose
docker compose up -d
```

### 2. 访问应用

- **应用地址**: http://localhost:8080
- **数据库**: localhost:3306
- **Redis**: localhost:6379
- **Mailpit UI**: http://localhost:8025

### 3. 初始化 Laravel

```bash
# 生成应用密钥
./docker.sh artisan key:generate

# 运行数据库迁移
./docker.sh artisan migrate

# 创建存储链接
./docker.sh artisan storage:link
```

## 📝 常用命令

### 环境管理

```bash
# 启动
./docker.sh start

# 停止
./docker.sh stop

# 重启
./docker.sh restart

# 查看状态
./docker.sh status

# 查看日志
./docker.sh logs
```

### Laravel Artisan 命令

```bash
# 进入容器
./docker.sh shell

# 运行 artisan 命令
./docker.sh artisan migrate
./docker.sh artisan make:model User
./docker.sh artisan make:controller UserController

# 清理缓存
./docker.sh artisan config:clear
./docker.sh artisan cache:clear
./docker.sh artisan route:clear

# 重新构建缓存
./docker.sh artisan config:cache
./docker.sh artisan route:cache
./docker.sh artisan view:cache
```

### Composer 命令

```bash
# 安装依赖
./docker.sh composer install

# 更新依赖
./docker.sh composer update

# 添加依赖
./docker.sh composer require package-name
```

### Docker Compose 命令

```bash
# 查看服务状态
docker compose ps

# 查看日志
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f mysql

# 重启服务
docker compose restart app

# 停止并删除容器
docker compose down

# 重新构建镜像
docker compose build --no-cache

# 清理未使用的资源
docker system prune -a
```

## 🔧 环境配置

### 修改端口

编辑 `.env` 文件：

```env
APP_PORT=8080
DB_PORT=3306
REDIS_PORT=6379
MAIL_PORT=1025
MAIL_UI_PORT=8025
```

### 修改数据库配置

```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 配置 Mailpit

访问 http://localhost:8025 查看发送的邮件。

## 🛠️ 开发工具

### Portainer

可视化 Docker 管理界面：http://localhost:9000

首次访问需要创建管理员账户。

### 常用开发命令

```bash
# 创建模型和迁移
./docker.sh artisan make:model Article -m

# 创建控制器
./docker.sh artisan make:controller ArticleController

# 创建 Filament Resource
./docker.sh artisan make:filament-resource Article

# 运行测试
./docker.sh artisan test

# 队列监听
./docker.sh artisan queue:work
```

## 📊 服务健康检查

```bash
# 检查所有服务状态
docker compose ps

# 检查特定服务
docker inspect filament-app
docker inspect filament-mysql
docker inspect filament-redis
```

## 🔍 故障排除

### 容器无法启动

```bash
# 查看日志
docker compose logs app
docker compose logs mysql

# 重新构建
docker compose down
docker compose build --no-cache
docker compose up -d
```

### 权限问题

```bash
# 修复权限
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### 数据库连接问题

```bash
# 检查 MySQL 日志
docker compose logs mysql

# 重新创建数据库
docker compose exec mysql mysql -u root -p -e "DROP DATABASE laravel; CREATE DATABASE laravel;"
```

## 📚 更多信息

- [Laravel 文档](https://laravel.com/docs)
- [Filament 文档](https://filamentadmin.com/docs/)
- [Docker 文档](https://docs.docker.com/)
- [Portainer 文档](https://docs.portainer.io/)

## ⚠️ 注意事项

1. 首次启动需要构建镜像，可能需要几分钟时间
2. 确保端口 8080、3306、6379、9000、1025、8025 未被占用
3. 定期清理 Docker 资源以节省磁盘空间
4. 生产环境请修改默认密码和密钥

## 🎯 推荐工作流

1. **开发时**: `./docker.sh start` 启动环境
2. **编写代码**: 使用 IDE 编辑项目文件
3. **测试**: http://localhost:8080
4. **查看邮件**: http://localhost:8025
5. **管理 Docker**: http://localhost:9000
6. **结束时**: `./docker.sh stop` 停止环境

## 📞 获取帮助

```bash
# 查看帮助
./docker.sh help

# 查看服务状态
./docker.sh status
```
