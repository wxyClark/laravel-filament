# 🚀 自动构建与部署规范

> **运维阶段** | **GitHub Actions** | **CI/CD 流水线**

---

## 📋 概述

**目标：** 实现代码提交到生产部署的自动化

**工具链：**
- 代码托管：GitHub
- CI/CD：GitHub Actions
- 容器化：Docker
- 部署：Docker Compose

---

## 🎯 CI/CD 流程

```mermaid
graph LR
    A[代码提交] --> B[代码检查]
    B --> C[单元测试]
    C --> D[构建镜像]
    D --> E[推送仓库]
    E --> F[部署测试环境]
    F --> G[集成测试]
    G --> H[部署生产环境]
```

---

## 📝 GitHub Actions 配置

### 基础流水线

```yaml
# .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
      
      redis:
        image: redis:7
        ports:
          - 6379:6379
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: dom, curl, mbstring, zip, pdo, mysql, bcmath
          coverage: xdebug
      
      - name: Install Dependencies
        run: composer install --no-progress --prefer-dist
      
      - name: Prepare Environment
        run: |
          cp .env.testing .env
          php artisan key:generate
      
      - name: Run Migrations
        run: php artisan migrate --force
      
      - name: Run Tests
        run: php artisan test --coverage --coverage-clover=coverage.xml
      
      - name: Upload Coverage
        uses: codecov/codecov-action@v3
        with:
          files: coverage.xml
```

### Docker 构建

```yaml
# .github/workflows/docker.yml
name: Docker Build

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Build Docker Image
        run: docker build -t ${{ secrets.DOCKER_REGISTRY }}/${{ github.event.repository.name }}:${{ github.ref_name }} .
      
      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}
      
      - name: Push to Docker Hub
        run: docker push ${{ secrets.DOCKER_REGISTRY }}/${{ github.event.repository.name }}:${{ github.ref_name }}
```

---

## 🐳 Docker 配置

### Dockerfile

```dockerfile
# Dockerfile
FROM php:8.2-fpm

# 安装扩展
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制应用代码
COPY . .

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

# 设置权限
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### docker-compose.yml

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - app-network
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    container_name: nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./nginx/conf.d/:/etc/nginx/conf.d/
    networks:
      - app-network
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
    ports:
      - "3306:3306"
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - app-network

  redis:
    image: redis:7-alpine
    container_name: redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  mysql-data:
```

---

## 📋 部署流程

### 1. 开发环境

```bash
# 本地开发
docker-compose up -d

# 查看日志
docker-compose logs -f

# 停止服务
docker-compose down
```

### 2. 测试环境

```bash
# 构建镜像
docker-compose -f docker-compose.test.yml build

# 启动测试环境
docker-compose -f docker-compose.test.yml up -d

# 运行测试
docker-compose -f docker-compose.test.yml exec app php artisan test
```

### 3. 生产环境

```bash
# 拉取最新镜像
docker-compose -f docker-compose.prod.yml pull

# 滚动更新
docker-compose -f docker-compose.prod.yml up -d --no-deps app

# 清理旧镜像
docker image prune -f
```

---

## 📊 监控与告警

### 健康检查

```yaml
# docker-compose.yml
services:
  app:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3
```

### 日志收集

```yaml
# docker-compose.yml
services:
  app:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```

---

## 💡 最佳实践

1. **环境一致性**：开发、测试、生产环境使用相同 Docker 镜像
2. **自动化部署**：使用 CI/CD 自动化部署流程
3. **版本管理**：使用语义化版本标签
4. **回滚机制**：保留历史版本，支持快速回滚
5. **监控告警**：配置健康检查和告警通知

---

**版本**: v1.0 | **更新日期**: 2026-04-30
