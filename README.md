# laravel-filament

Laravel 12 + Filament 3.x 前后端不分离 AI 开发项目

基于 DDD（领域驱动设计）架构的企业级综合业务系统，包含电商、O2O、分销、RBAC、CRM、进销存、财务等核心模块。

---

## 🚀 快速开始

### ⚡ 一键初始化（推荐）

```bash
# 运行自动初始化脚本（约 5-10 分钟）
cd /home/clark/www/laravel-filament
./init-project.sh

# 创建管理员用户
docker compose exec app php artisan make:filament-user

# 访问应用
# 前台: http://localhost:8082
# Filament 后台: http://localhost:8082/admin
# Telescope 调试: http://localhost:8082/telescope
```

**初始化脚本会自动完成**：
- ✅ 安装 Filament 后台
- ✅ 安装核心工具包（PHPStan, Pest, IDE Helper, Telescope）
- ✅ 配置 RBAC 权限系统（spatie/laravel-permission）
- ✅ 创建 DDD 目录结构
- ✅ 生成 IDE Helper 文件
- ✅ 运行静态分析和测试

---

## 📦 已安装的工具链

### P0 优先级工具（已完成安装）

| 工具 | 版本 | 用途 | 状态 |
|------|------|------|------|
| **PHPStan + Larastan** | 2.1.54 + 3.9.6 | 静态代码分析 | ✅ 已安装 |
| **Pest PHP** | 3.8.6 | 现代化测试框架 | ✅ 已安装 |
| **Laravel IDE Helper** | 3.7.0 | IDE 类型提示 | ✅ 已安装 |
| **Laravel Telescope** | 5.20.0 | 调试面板 | ✅ 已安装 |
| **Spatie Permission** | 6.25.0 | RBAC 权限控制 | ✅ 已安装 |

详细安装报告请查看：[doc/TOOLCHAIN_INSTALLATION_REPORT.md](doc/TOOLCHAIN_INSTALLATION_REPORT.md)

### 常用命令

```bash
# 静态分析
docker compose exec app ./vendor/bin/phpstan analyse

# 运行测试
docker compose exec app ./vendor/bin/pest

# 代码格式化
docker compose exec app ./vendor/bin/pint

# 重新生成 IDE Helper
docker compose exec app php artisan ide-helper:generate
docker compose exec app php artisan ide-helper:models --write

# Telescope 数据管理
docker compose exec app php artisan telescope:clear
docker compose exec app php artisan telescope:prune --hours=24
```

---

### 📋 手动安装（传统方式）

#### 环境要求

- Docker & Docker Compose V2
- Node.js 18+ (用于 MCP 服务)
- Git
- PHP 8.5+ (本地运行时需要，Docker 内已包含所有扩展)
  - 扩展: xml, mbstring, sqlite3, curl, zip, mysql, redis

### 启动项目

```bash
# 1. 确保共享基础设施已启动
cd /home/clark/www/infrastructure
docker compose up -d

# 2. 启动本项目
cd /home/clark/www/laravel-filament
docker compose up -d

# 3. 安装依赖
docker compose exec app composer install

# 4. 生成应用密钥
docker compose exec app php artisan key:generate

# 5. 执行数据库迁移
docker compose exec app php artisan migrate

# 6. 访问应用
# 前台: http://localhost:8082
# Filament 后台: http://localhost:8082/admin
```

---

## 🛠️ 开发环境与依赖

### MCP (Model Context Protocol) 服务配置

本项目配置了以下 MCP 服务以增强 AI 辅助开发能力：

#### 已配置的 MCP 服务

| 服务名称 | NPM 包 | 作用 | 连接目标 |
|---------|--------|------|----------|
| **filesystem** | `@modelcontextprotocol/server-filesystem` | 文件系统操作（读取、搜索、管理项目文件） | `/home/clark/www/laravel-filament` |
| **git** | `@modelcontextprotocol/server-git` | Git 历史查询（提交记录、分支、代码变更） | 项目 Git 仓库 |
| **puppeteer** | `@modelcontextprotocol/server-puppeteer` | 浏览器自动化（测试 Filament 页面、截图、UI 验证） | 本地 Chrome/Chromium |
| **sequential-thinking** | `@modelcontextprotocol/server-sequential-thinking` | 复杂任务分解（多步骤思考与问题分析） | - |
| **mysql** | `@modelcontextprotocol/server-sql` | MySQL 数据库查询（表结构、数据检索、SQL 执行） | `localhost:3306` (infra-mysql) |
| **redis** | `@modelcontextprotocol/server-redis` | Redis 缓存操作（查看缓存、会话、队列数据） | `localhost:6379` (infra-redis) |

#### 安装步骤

```bash
# 1. 确保已安装 Node.js 18+
node --version

# 2. MCP 服务会通过 npx 自动安装，无需手动安装
# 首次使用时会自动下载相关包

# 3. 验证 Puppeteer 依赖（需要 Chromium）
npx puppeteer browsers install chrome
```

#### 配置文件位置

MCP 服务配置位于项目根目录的 `.mcp.json` 文件。

#### 数据库连接说明

- **MySQL**: 连接到共享基础设施的 `infra-mysql` 容器（通过 localhost:3306 映射）
  - 数据库: `db_laravel-filament`
  - 用户名: `user_laravel-filament`
  - 密码: 见 `.env` 文件的 `DB_PASSWORD`

- **Redis**: 连接到共享基础设施的 `infra-redis` 容器（通过 localhost:6379 映射）
  - 无密码认证
  - 用于缓存、会话、队列驱动

- **PostgreSQL**: ❌ 本项目未使用 PostgreSQL，故未配置相关 MCP 服务

#### 禁用/启用 MCP 服务

如需临时禁用某个 MCP 服务，在 `.mcp.json` 中设置 `"disabled": true`：

```json
{
  "mcpServers": {
    "puppeteer": {
      "disabled": true,
      ...
    }
  }
}
```

---

## 📦 技术栈

- **后端框架**: Laravel 12.x (PHP 8.5+)
- **后台管理**: Filament 3.x (Livewire 3)
- **数据库**: MySQL 8.4 (共享基础设施)
- **缓存/会话**: Redis 7.0 (共享基础设施)
- **消息队列**: RabbitMQ 3.12 (共享基础设施)
- **Web 服务器**: Nginx Alpine
- **容器化**: Docker & Docker Compose
- **测试框架**: Pest PHP (SQLite :memory:)
- **代码规范**: Laravel Pint (PSR-12)
- **静态分析**: PHPStan Level 5 + Larastan

---

## 🏗️ 项目架构

采用 DDD（领域驱动设计）分层架构：

```
app/
├── Domains/                  # 领域边界
│   ├── User/                 # 用户域 (RBAC, Customer)
│   ├── Product/              # 商品域 (SPU, SKU, Category)
│   ├── Trade/                # 交易域 (Order, Cart, Payment)
│   ├── O2O/                  # 预约域 (Appointment, Store)
│   └── Distribution/         # 分销域 (Commission, Relationship)
├── Infrastructure/           # 基础设施层
├── Filament/                 # 后台资源
└── Http/                     # 接入层
```

详细架构说明请参考：[doc/design/01-architecture-spec.md](doc/design/01-architecture-spec.md)

---

## 📚 文档

### 📊 项目审计与规划
- [**项目审计报告**](doc/AUDIT_REPORT_2026-05-01.md) - 深度分析当前状态、工具链推荐、功能规划
- [**快速初始化指南**](init-project.sh) - 一键安装核心工具包和配置环境

### 📝 需求与设计
- [PRD 需求文档](doc/PRD/00-PRD-INDEX.md)
- [架构设计规范](doc/design/01-architecture-spec.md)
- [测试策略](doc/design/02-testing-strategy.md)
- [Laravel Boost 审计报告](doc/design/03-laravel-boost-audit-report.md)

### 🎓 AI 辅助开发
- [AI 开发最佳实践](doc/BestPractice/README.md)
- [学科思维方法论](doc/Core/README.md)

### 🐳 基础设施
- [Docker 环境说明](docker/README.md)

### 🔧 运维管理工具
- **Portainer**: http://localhost:9000 — Docker 容器管理面板
  - 账号: `admin`
  - 密码: `admin@example`

---

## 🔧 常用命令

### 项目初始化
```bash
./init-project.sh                    # 一键初始化（推荐）
```

### Docker 操作
# Docker 操作
docker compose up -d                    # 启动服务
docker compose down                     # 停止服务
docker compose logs -f app              # 查看应用日志
docker compose exec app bash            # 进入应用容器

### Laravel Artisan
```bash
docker compose exec app php artisan migrate          # 执行迁移
docker compose exec app php artisan db:seed          # 填充数据
docker compose exec app php artisan app:reset        # 安全重置（migrate:fresh + seed）
docker compose exec app php artisan app:reset --snapshot  # 从快照恢复（秒级完成）
docker compose exec app php artisan route:list       # 查看路由
docker compose exec app php artisan queue:work       # 启动队列 worker
docker compose exec app php artisan make:filament-user # 创建 Filament 管理员
```

> ⚠️ **禁止直接运行 `migrate:fresh`**：会清空所有数据。请使用 `php artisan app:reset`。
> 使用 `--snapshot` 选项可从快照恢复地址数据（秒级完成，跳过慢速 seeder）。

### 数据快照管理

```bash
# 创建/更新地址数据快照
docker compose exec mysql mysqldump -u root -psecret laravel addresses > database/addresses_snapshot.sql

# 从快照恢复（推荐）
php artisan app:reset --snapshot
```

### 代码质量
```bash
# Docker 内运行
docker compose exec app ./vendor/bin/pint            # 代码格式化
docker compose exec app ./vendor/bin/phpstan analyse # 静态分析
docker compose exec app ./vendor/bin/pest            # 运行测试
docker compose exec app ./vendor/bin/pest --coverage # 测试覆盖率

# 宿主机本地运行（需先安装 PHP 扩展）
./vendor/bin/pint                                    # 代码格式化
./vendor/bin/phpstan analyse                         # 静态分析
./vendor/bin/pest --compact                          # 运行测试
```

### IDE Helper
```bash
docker compose exec app php artisan ide-helper:generate # 生成 Facade 提示
docker compose exec app php artisan ide-helper:meta     # 生成 PhpStorm meta
docker compose exec app php artisan ide-helper:models   # 生成模型属性提示
```

---

## 💻 本地开发环境

项目支持两种运行方式：Docker 容器内运行（推荐）和宿主机本地运行。

### 方式一：Docker 内运行（推荐）

```bash
# 所有命令加 docker compose exec app 前缀
docker compose exec app ./vendor/bin/pest
docker compose exec app ./vendor/bin/pint
docker compose exec app ./vendor/bin/phpstan analyse
```

### 方式二：宿主机本地运行

#### 1. 安装 PHP 扩展

```bash
sudo apt-get install -y php8.5-xml php8.5-mbstring php8.5-sqlite3 php8.5-curl php8.5-zip php8.5-mysql php8.5-redis
```

| 扩展 | 用途 |
|------|------|
| `php8.5-xml` | DOMDocument（Pest 依赖） |
| `php8.5-mbstring` | 多字节字符串处理（Termwind 依赖） |
| `php8.5-sqlite3` | 测试数据库驱动（PHPUnit 使用 SQLite :memory:） |
| `php8.5-curl` | HTTP 请求 |
| `php8.5-zip` | Composer 依赖管理 |
| `php8.5-mysql` | MySQL 数据库连接 |
| `php8.5-redis` | Redis 缓存连接 |

#### 2. 修复目录权限

Docker 容器以 root 创建的文件，宿主机用户需要修改所有权：

```bash
sudo chown -R $(id -u):$(id -g) storage bootstrap/cache vendor/pestphp/pest/.temp
```

#### 3. 本地运行测试

```bash
# 运行全部测试
./vendor/bin/pest --compact

# 运行指定测试文件
./vendor/bin/pest tests/Feature/Filament/Address/AddressCascadeTest.php

# 按名称筛选
./vendor/bin/pest --filter="cascade"

# 代码格式化
./vendor/bin/pint

# 静态分析
./vendor/bin/phpstan analyse
```

---

## 🧪 测试

### 测试结构

```
tests/
├── Pest.php                          # 全局配置（RefreshDatabase）
├── Feature/
│   ├── Api/Auth/                     # JWT 认证测试
│   ├── Auth/                         # Session 认证 + 模型测试
│   ├── Filament/
│   │   ├── Address/                  # 地址列表页 + 级联筛选测试
│   │   └── Auth/                     # Filament 面板认证测试
│   └── AddressApiTest.php            # 地址 API 测试
└── Unit/
    ├── Models/AddressTest.php        # 地址模型行为测试
    └── Services/AddressServiceTest.php # 地址服务逻辑测试
```

### 运行测试

```bash
# Docker 内运行
docker compose exec app ./vendor/bin/pest --compact

# 宿主机本地运行
./vendor/bin/pest --compact

# 运行指定目录
./vendor/bin/pest tests/Feature/Filament/Address/

# 按名称筛选
./vendor/bin/pest --filter="cascade"

# 查看详细输出
./vendor/bin/pest --compact=false
```

### 测试约定

- 使用 `test()` 函数（非 `it()`）
- 每个 Service 方法至少 1 个单元测试
- 每个 API 端点至少 1 个集成测试
- 使用工厂创建测试数据：`Admin::factory()->create()`
- 全局 RefreshDatabase 自动清理数据

---

## 📝 许可证

MIT License
