# Docker 目录结构最佳实践指南

## 📋 当前状态分析

### 现有结构

```
laravel-filament/docker/
├── Dockerfile          # ✅ PHP-FPM 镜像（应用特定）
├── nginx/
│   └── default.conf    # ✅ Nginx 配置（应用特定）
├── backup.sh           # ❌ 应该移到 infrastructure
├── backup/             # ❌ 应该移到 infrastructure
└── README.md           # ⚠️ 内容需要调整
```

### 问题分析

1. **职责不清**：`backup.sh` 是基础设施级别的脚本，不应该在应用项目中
2. **重复维护**：如果每个项目都有备份脚本，会导致代码重复
3. **边界模糊**：应用项目不应该直接管理数据库备份

---

## 🎯 推荐的目录结构

### 方案：分层架构（推荐）⭐⭐⭐⭐⭐

#### 1. 应用项目层（laravel-filament）

```
laravel-filament/
├── docker/                    # 只包含应用特定的 Docker 配置
│   ├── php/
│   │   └── Dockerfile        # PHP-FPM 镜像定义
│   ├── nginx/
│   │   └── default.conf      # Nginx 配置
│   └── README.md             # 应用 Docker 使用说明
├── compose.yaml               # 只定义 app + nginx
├── .env
└── ...
```

**原则**：
- ✅ 只包含应用容器相关的配置
- ✅ 不包含数据库、Redis 等基础设施
- ✅ 通过外部网络连接到共享基础设施

#### 2. 基础设施层（infrastructure）

```
infrastructure/
├── docker-compose.yml         # MySQL, Redis, RabbitMQ
├── backup.sh                  # ✅ 统一备份脚本
├── backup/                    # ✅ 统一备份目录
├── scripts/
│   └── manage-databases.sh   # 数据库管理
├── .env
└── README.md
```

**原则**：
- ✅ 统一管理所有共享服务
- ✅ 统一备份策略
- ✅ 统一监控和运维

---

## 🔄 迁移步骤

### 步骤 1：清理应用项目中的基础设施文件

```bash
# 进入 laravel-filament 项目
cd /home/clark/www/laravel-filament

# 移除基础设施相关文件
rm -rf docker/backup.sh
rm -rf docker/backup/
```

### 步骤 2：更新应用项目的 README

创建新的 `docker/README.md`，只关注应用容器：

```markdown
# Laravel Filament Docker 配置

本目录包含 Laravel Filament 应用的 Docker 配置。

## 📁 文件说明

- `php/Dockerfile` - PHP 8.2-FPM 镜像定义
- `nginx/default.conf` - Nginx 配置文件

## 🚀 使用方法

### 构建镜像

```bash
docker compose build app
```

### 启动应用

```bash
docker compose up -d
```

### 进入容器

```bash
docker compose exec app bash
```

## 🔗 依赖服务

本应用依赖以下共享基础设施服务：
- MySQL: `infra-mysql` (端口 3306)
- Redis: `infra-redis` (端口 6379)
- RabbitMQ: `infra-rabbitmq` (端口 5672)

请确保基础设施已启动：
```bash
cd /home/clark/www/infrastructure && docker compose up -d
```

## 📝 注意事项

- 数据库备份请在基础设施层执行
- 查看基础设施文档：`/home/clark/www/infrastructure/README.md`
```

### 步骤 3：更新 compose.yaml

确保 `compose.yaml` 只包含应用服务，并连接到基础设施网络：

```yaml
services:
  app:
    build:
      context: .
      dockerfile: ./docker/php/Dockerfile
    container_name: filament-app
    volumes:
      - '../:/var/www/html'
    networks:
      - laravel-net
      - infrastructure-network  # 连接基础设施
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy

  nginx:
    image: nginx:alpine
    container_name: filament-nginx
    ports:
      - '8082:80'
    volumes:
      - '../:/var/www/html'
      - './docker/nginx/default.conf:/etc/nginx/conf.d/default.conf'
    networks:
      - laravel-net
    depends_on:
      - app

networks:
  laravel-net:
    driver: bridge
  infrastructure-network:
    external: true
```

---

## 📊 对比分析

### 迁移前 ❌

```
laravel-filament/
├── docker/
│   ├── Dockerfile
│   ├── nginx/
│   ├── backup.sh          # ← 与应用无关
│   └── backup/            # ← 与应用无关
├── compose.yaml           # ← 包含 MySQL, Redis, RabbitMQ
└── ...

问题：
- 职责不清
- 资源浪费（多个 MySQL 实例）
- 备份分散
- 难以统一管理
```

### 迁移后 ✅

```
infrastructure/            # ← 统一管理
├── docker-compose.yml     # MySQL, Redis, RabbitMQ
├── backup.sh              # 统一备份
└── backup/

laravel-filament/          # ← 只关注应用
├── docker/
│   ├── php/Dockerfile
│   └── nginx/default.conf
├── compose.yaml           # 只包含 app + nginx
└── ...

优势：
- 职责清晰
- 资源共享
- 统一备份
- 易于扩展
```

---

## 🏆 行业最佳实践总结

### 1. 单一职责原则

- **应用项目**：只管理应用容器（App、Nginx、Worker）
- **基础设施**：管理共享服务（DB、Cache、Queue）

### 2. 关注点分离

```
应用层关心：
- 业务逻辑
- 应用配置
- 前端资源

基础设施层关心：
- 数据存储
- 缓存策略
- 消息队列
- 备份恢复
```

### 3. 可复用性

- 基础设施可以服务于多个应用
- 应用可以快速切换基础设施环境（开发/测试/生产）

### 4. 版本控制

- 应用代码和 Docker 配置一起版本控制
- 基础设施配置独立版本控制

---

## 💡 具体建议

### 对于 laravel-filament 项目

#### 立即执行：

1. **移除基础设施文件**
   ```bash
   cd /home/clark/www/laravel-filament
   rm -rf docker/backup.sh docker/backup/
   ```

2. **重组 docker 目录**
   ```bash
   mkdir -p docker/php
   mv docker/Dockerfile docker/php/Dockerfile
   ```

3. **更新文档**
   - 重写 `docker/README.md`
   - 删除关于数据库备份的内容
   - 添加指向基础设施文档的链接

4. **更新 compose.yaml**
   - 移除 MySQL、Redis、RabbitMQ 服务定义
   - 添加 `infrastructure-network` 外部网络

#### 长期优化：

1. **多环境支持**
   ```
   docker/
   ├── php/
   │   ├── Dockerfile.dev
   │   ├── Dockerfile.staging
   │   └── Dockerfile.prod
   └── nginx/
       ├── default.dev.conf
       └── default.prod.conf
   ```

2. **CI/CD 集成**
   - 在 CI 中使用不同的 Docker 配置
   - 自动化构建和测试

3. **镜像优化**
   - 使用多阶段构建
   - 减小镜像体积
   - 提高构建速度

---

## 📚 参考资源

### 官方文档
- [Docker 最佳实践](https://docs.docker.com/develop/dev-best-practices/)
- [Laravel Docker 部署](https://laravel.com/docs/deployment)
- [Docker Compose 最佳实践](https://docs.docker.com/compose/production/)

### 相关文章
- [Microservices Architecture with Docker](https://microservices.io/)
- [12-Factor App Methodology](https://12factor.net/)

### 社区实践
- Laravel Sail 的项目结构
- Docker Official Images 的组织方式

---

## ✅ 检查清单

优化完成后，确认：

- [ ] 应用项目只包含应用相关的 Docker 配置
- [ ] 基础设施配置在独立的 `infrastructure` 项目
- [ ] 备份脚本在基础设施层统一管理
- [ ] 应用通过外部网络连接基础设施
- [ ] 文档清晰说明了各层的职责
- [ ] compose.yaml 只定义应用服务

---

**总结**：在 Laravel 项目下创建 `docker` 目录是**符合最佳实践**的，但需要明确其职责边界。应用项目的 `docker` 目录应该只包含**应用特定**的配置，而共享的基础设施应该在独立的项目中管理。

**最后更新**: 2026-04-27
