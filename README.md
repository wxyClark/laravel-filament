# laravel-filament

Laravel 12 + Filament 3.x 前后端不分离 AI 开发项目

基于 DDD（领域驱动设计）架构的企业级综合业务系统，包含电商、O2O、分销、RBAC、CRM、进销存、财务等核心模块。

---

## 🚀 快速开始

### 环境要求

- Docker & Docker Compose V2
- Node.js 18+ (用于 MCP 服务)
- Git

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

- **后端框架**: Laravel 12.x (PHP 8.2+)
- **后台管理**: Filament 3.x (Livewire 3)
- **数据库**: MySQL 8.4 (共享基础设施)
- **缓存/会话**: Redis 7.0 (共享基础设施)
- **消息队列**: RabbitMQ 3.12 (共享基础设施)
- **Web 服务器**: Nginx Alpine
- **容器化**: Docker & Docker Compose

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

- [PRD 需求文档](doc/PRD/00-PRD-INDEX.md)
- [架构设计规范](doc/design/01-architecture-spec.md)
- [测试策略](doc/design/02-testing-strategy.md)
- [Laravel Boost 审计报告](doc/design/03-laravel-boost-audit-report.md)
- [AI 开发最佳实践](doc/BestPractice/README.md)
- [Docker 环境说明](docker/README.md)

---

## 🔧 常用命令

```bash
# Docker 操作
docker compose up -d                    # 启动服务
docker compose down                     # 停止服务
docker compose logs -f app              # 查看应用日志
docker compose exec app bash            # 进入应用容器

# Laravel Artisan
docker compose exec app php artisan migrate          # 执行迁移
docker compose exec app php artisan db:seed          # 填充数据
docker compose exec app php artisan route:list       # 查看路由
docker compose exec app php artisan queue:work       # 启动队列 worker

# 测试
docker compose exec app ./vendor/bin/pest            # 运行测试
docker compose exec app ./vendor/bin/pint            # 代码格式化
```

---

## 📝 许可证

MIT License
