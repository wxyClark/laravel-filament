---
name: techstack-installer
description: "Use this skill to intelligently install and configure the complete Laravel Filament tech stack. Trigger when: 'setup project', 'install dependencies', 'configure environment', 'init project', 'deploy local', or any first-time setup. Covers: Docker environment, PHP extensions, Composer packages, Node.js dependencies, database setup, Redis, Filament installation, RBAC, input methods, and environment configuration. Automatically detects missing components and suggests fixes."
license: MIT
metadata:
  author: laravel-filament
  version: 2.0.0
---

# Tech Stack Installer — 智能技术栈安装

> **技术栈**: Laravel 12 + Filament 3.x + PHP 8.5 + MySQL 8.0 + Redis 7.0
> **架构**: DDD 分层 (Domain / Infrastructure / Http)
> **安装方式**: Docker Compose (推荐) 或 本地环境

---

## 核心原则

```
✅ 检测 → 诊断 → 修复 → 验证
✅ 幂等性：重复执行不报错
✅ 自动回滚：失败时恢复状态
✅ 清晰输出：每步显示进度
```

---

## 环境检测矩阵

### 1. Docker 环境检测

```bash
# 检测 Docker 是否安装
docker --version

# 检测 Docker Compose 是否安装
docker compose version

# 检测 Docker 服务是否运行
docker info

# 检测容器状态
docker compose ps
```

### 2. PHP 环境检测

```bash
# 检测 PHP 版本 (需要 8.5+)
php -v

# 检测必要扩展
php -m | grep -E "curl|mbstring|xml|sqlite3|pdo_mysql|redis|pcntl"

# 检测 Composer
composer --version
```

### 3. Node.js 环境检测

```bash
# 检测 Node.js 版本
node --version

# 检测 npm
npm --version

# 检测 pnpm (可选)
pnpm --version
```

### 4. 数据库检测

```bash
# 检测 MySQL 连接
mysql -u root -p -e "SELECT VERSION();"

# 检测 Redis 连接
redis-cli ping
```

---

## 安装流程

### Phase 1: Docker 环境

```bash
# 启动容器
docker compose up -d

# 等待就绪
sleep 10

# 验证容器
docker compose ps
```

### Phase 2: PHP 依赖

```bash
# 安装 Composer 依赖
docker compose exec app composer install --no-interaction

# 生成应用密钥
docker compose exec app php artisan key:generate

# 运行迁移
docker compose exec app php artisan migrate

# 填充数据
docker compose exec app php artisan db:seed
```

### Phase 3: 前端依赖

```bash
# 安装 Node.js 依赖
docker compose exec app npm install

# 构建前端资源
docker compose exec app npm run build
```

### Phase 4: Filament 配置

```bash
# 安装 Filament
docker compose exec app php artisan filament:install --panels

# 创建管理员
docker compose exec app php artisan make:filament-user

# 安装 RBAC (Spatie)
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Phase 5: 输入法配置

```bash
# 安装 Fcitx5
sudo apt install -y fcitx5 fcitx5-chinese-addons fcitx5-frontend-gtk3 fcitx5-frontend-gtk4 fcitx5-frontend-qt5

# 配置输入法环境变量
echo 'export INPUT_METHOD=fcitx' >> ~/.zshrc
export GTK_IM_MODULE=fcitx
export QT_IM_MODULE=fcitx
export XMODIFIERS=@im=fcitx
```

---

## 智能诊断

### 常见问题检测

```php
// 检测 PHP 扩展缺失
$requiredExtensions = ['curl', 'mbstring', 'xml', 'sqlite3', 'pdo_mysql', 'redis'];
$missing = array_diff($requiredExtensions, get_loaded_extensions());

// 检测目录权限
$writableDirs = ['storage', 'bootstrap/cache', 'vendor/pestphp/pest/.temp'];
foreach ($writableDirs as $dir) {
    if (!is_writable($dir)) {
        echo "目录不可写: $dir\n";
    }
}

// 检测数据库连接
try {
    DB::connection()->getPdo();
} catch (Exception $e) {
    echo "数据库连接失败: " . $e->getMessage();
}
```

### 自动修复脚本

```bash
#!/bin/bash
# scripts/fix-environment.sh

# 修复目录权限
sudo chown -R $(id -u):$(id -g) storage bootstrap/cache vendor/pestphp/pest/.temp

# 修复 PHP 扩展
sudo apt install -y php8.5-xml php8.5-mbstring php8.5-sqlite3 php8.5-redis

# 修复 Composer 缓存
composer clear-cache

# 修复 npm 缓存
npm cache clean --force
```

---

## 验证清单

### 必须通过

- [ ] Docker 容器运行正常
- [ ] PHP 版本 >= 8.5
- [ ] 所有必要的 PHP 扩展已加载
- [ ] Composer 依赖安装完成
- [ ] Node.js 依赖安装完成
- [ ] 数据库迁移完成
- [ ] Filament 面板可访问
- [ ] 管理员账户已创建
- [ ] 目录权限正确

### 可选验证

- [ ] Redis 连接正常
- [ ] 前端资源构建完成
- [ ] 测试通过
- [ ] 输入法配置完成

---

## 环境配置文件

### .env.example

```env
APP_NAME="Laravel Filament"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8082

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile
    container_name: laravel-filament-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - laravel-filament

  nginx:
    image: nginx:alpine
    container_name: laravel-filament-nginx
    restart: unless-stopped
    ports:
      - "8082:80"
    volumes:
      - .:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    networks:
      - laravel-filament

  mysql:
    image: mysql:8.0
    container_name: laravel-filament-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-laravel}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD:-secret}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - laravel-filament

  redis:
    image: redis:7-alpine
    container_name: laravel-filament-redis
    restart: unless-stopped
    networks:
      - laravel-filament

networks:
  laravel-filament:
    driver: bridge

volumes:
  mysql-data:
```

---

## 快速开始命令

```bash
# 一键安装
./init-project.sh

# 仅检测环境
./scripts/check-environment.sh

# 修复环境问题
./scripts/fix-environment.sh

# 重新安装
./init-project.sh --fresh
```
