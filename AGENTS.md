# AGENTS.md

## Quick Commands

All commands run inside Docker. Prefix: `docker compose exec app`

```bash
./vendor/bin/pint              # Format code (dry-run in CI: pint --test)
./vendor/bin/phpstan analyse   # Static analysis (level 5)
./vendor/bin/pest              # Run tests
./vendor/bin/pest --parallel   # Run tests in parallel (CI mode)
```

Code quality order: `pint -> phpstan -> pest`

## Architecture

Laravel 12 + Filament 3.x, PHP 8.5+, DDD-style layering.

```
app/
├── Domains/          # Business domains (User, Product, Trade, O2O, Distribution, etc.)
│   ├── {Domain}/
│   │   ├── Models/       # Eloquent models (with SoftDeletes, HasFactory)
│   │   ├── Enums/        # Status enums (BackedEnum with label(), color())
│   │   ├── Services/     # Business logic (thin controllers, fat services)
│   │   ├── Data/         # DTOs (readonly class implementing Arrayable)
│   │   ├── Events/       # Domain events
│   │   ├── Repositories/ # Repository interfaces
│   │   └── Policies/     # Authorization policies
├── Infrastructure/   # Infrastructure layer — contains Filament/Resources/ (checked by CI)
│   ├── Filament/Resources/{Domain}/{Entity}Resource.php  # CRUD resources
│   ├── Filament/Resources/{Domain}/Pages/                # List/Create/Edit pages
│   ├── Filament/Widgets/                                 # Dashboard widgets
│   ├── Repositories/Eloquent/                            # Repository implementations
│   └── Support/Traits/                                   # Shared traits
├── Filament/         # Panel resources: Admin/ and Public/ (auto-discovered by panel)
├── Http/             # Controllers (thin), FormRequests, API Resources
├── Models/           # Cross-domain shared models only
└── Services/         # Shared infrastructure services
```

## Key Gotchas

- **Two compose files**: `compose.yaml` connects to shared infrastructure (MySQL/Redis via external network). `docker-compose.yml` is standalone with embedded MySQL/Redis. The README and init script use `compose.yaml`.
- **Filament resources exist in two places**: `app/Filament/Admin/Resources` (auto-discovered by AdminPanelProvider) and `app/Infrastructure/Filament/Resources` (CI checks these for required `form()` and `table()` methods).
- **Tests are Pest, not PHPUnit.** Global config in `tests/Pest.php` applies `RefreshDatabase` to all Feature/Unit tests.
- **`declare_strict_types`** is enforced by `.php-cs-fixer.php`. All new PHP files must include it.
- **PHPStan is level 5** (`phpstan.neon`). Don't lower it.
- **CI enforces `pint --test`** (dry-run) — run `pint` locally before pushing to catch formatting issues.
- **DDD layer boundaries**: Domain → no framework deps; Infrastructure → implements Domain interfaces; Http → calls Service only.
- **金额字段**: 必须使用 `decimal(10, 2)`，严禁 FLOAT/DOUBLE。
- **软删除**: 核心业务表必须开启 SoftDeletes。
- **Seeder 内存**: AddressSeeder 需要 512M 内存 + 禁用 Telescope (`TELESCOPE_ENABLED=false`)。
- **本地运行测试**: 需安装 `php8.5-xml php8.5-mbstring php8.5-sqlite3`。
- **目录权限**: Docker 创建的文件需要 `sudo chown -R $(id -u):$(id -g) storage bootstrap/cache vendor/pestphp/pest/.temp`。
- **禁止直接运行 `migrate:fresh`**：会清空所有数据。必须使用 `php artisan app:reset`（自动 seed）。
  - `--snapshot` 选项：从快照恢复地址数据（秒级完成，跳过慢速 seeder）

## 铁律（不可违反）

### 数据安全规则

1. **禁止擅自删除数据**：任何涉及删除数据库数据的操作（migrate:fresh、truncate、drop、forceDelete）必须先向用户确认
2. **禁止擅自清空缓存/会话**：Redis 清空、Session 清除等操作必须确认
3. **确认格式**：`即将执行 [操作]，将删除 [具体数据]，是否继续？`
4. **例外情况**：测试环境（SQLite :memory:）的 RefreshDatabase 不需要确认

## Testing

```bash
./vendor/bin/pest                              # All tests
./vendor/bin/pest tests/Feature/AddressApiTest.php  # Single test file
./vendor/bin/pest --filter="testName"          # By name
```

Tests require a running database. Ensure Docker containers are up first.

### Test Organization

```
tests/
├── Unit/Domains/{Domain}/         # Service/Model/DTO unit tests
├── Feature/Api/                   # API integration tests
├── Feature/Filament/              # Filament resource tests (Livewire::test)
└── Pest.php                       # Global config (RefreshDatabase)
```

### Test Conventions

- Follow `test()` style (not `it()`) — check existing files for convention
- AAA pattern: Arrange → Act → Assert
- Use factories for test data: `Order::factory()->create()`
- Use specific assertions: `assertSuccessful()`, `assertNotFound()` over `assertStatus()`
- Each Service method: minimum 1 unit test
- Each API endpoint: minimum 1 integration test

## Code Standards

### Must Pass (before every commit)

```bash
./vendor/bin/pint --test          # Code style
./vendor/bin/phpstan analyse      # Static analysis
./vendor/bin/pest --compact       # Tests
```

### Key Rules

- **Pint**: PSR-12 + `declare_strict_types` + ordered imports + short array syntax
- **PHPStan Level 5**: Return types, parameter types, nullable handling
- **Filament Resource**: Must implement `form()` and `table()` methods
- **Class member order**: trait → constant → property → constructor → method

## Setup

```bash
./init-project.sh              # One-shot init (~5-10 min): installs Filament, tools, RBAC, runs migrations
docker compose exec app php artisan make:filament-user  # Create admin user after init
```

App: `http://localhost:8082` | Admin: `http://localhost:8082/admin`

## Skills & Workflow

### 标准工作流（强制执行）

收到用户提示词后，按以下流程执行：

```
用户输入 → 理解(多轮对话) → 增强 → 确认 → 执行 → 验证
```

1. **理解（多轮对话挖掘）**：通过追问深度挖掘需求，确保逻辑闭环、全局一致
2. **增强**：读取 PRD/rules/skills，形成结构化提示词
3. **确认**：呈现增强后的提示词，有模糊处提出疑问
4. **执行**：用户确认后，按约束条件编写代码
5. **验证**：Pint + PHPStan + Pest + 架构测试 + 文档更新

详细流程见：[PROMPT_WORKFLOW.md](PROMPT_WORKFLOW.md)

### TDD Workflow (8 steps)

1. **需求分析** → Read PRD docs in `doc/PRD/{module}/`
2. **架构设计** → Plan files per DDD layer
3. **数据库设计** → Create migration (`make:migration`)
4. **先写测试** → Red phase (Pest tests)
5. **实现代码** → Green phase (minimal code to pass)
6. **重构优化** → Refactor phase (Pint + PHPStan)
7. **架构测试** → Architecture phase (DDD boundaries + naming)
8. **联调验证** → End-to-end verification

### Skills Directory

| Skill | Purpose |
|-------|---------|
| `prompt-engineer` | 优化提示词，生成结构化高质量需求 |
| `requirement-analysis` | Multi-turn dialogue for requirement discovery + logic validation |
| `tdd-workflow` | Complete AI-assisted TDD flow (8 steps) |
| `architecture-testing` | DDD layer boundaries + naming conventions |
| `techstack-installer` | Intelligent tech stack installation |
| `reflection-improvement` | AI agent self-reflection and improvement |
| `git-workflow` | Git workflow and commit conventions |
| `code-standards` | Pint + PHPStan checking |
| `code-review` | Code review with static analysis |
| `database-design` | Migration & schema conventions |
| `laravel-architecture` | DDD patterns, services, repos |
| `laravel-best-practices` | 19 rule categories with examples |
| `filament-development` | Filament resource conventions |
| `pest-testing` | Pest PHP testing patterns |
| `pint-code-style` | Code formatting rules |
| `restful-api-routing` | API route design |
| `mysql-best-practices` | Query optimization |
| `queue-jobs-best-practices` | Job/queue patterns |
| `redis-best-practices` | Caching strategies |

### Document Structure

```
doc/
├── requirements/          # 需求分析文档 (REQ-{模块}-{序号})
├── design/               # 设计文档 (DES-{模块}-{序号})
├── development/          # 开发文档 (DEV-{模块}-{序号})
├── testing/              # 测试文档 (TES-{模块}-{序号})
├── deployment/           # 部署文档 (DEP-{模块}-{序号})
├── retrospective/        # 回顾文档 (RET-{模块}-{序号})
├── PRD/                  # 产品需求文档
├── BestPractice/         # 最佳实践
└── Core/                 # 核心文档
```

### Document Lifecycle

```
草稿 → 评审中 → 已确认 → 已废弃
```

## References

- CI pipeline: `.github/workflows/ci.yml`
- Code style rules: `.php-cs-fixer.php`
- PHPStan config: `phpstan.neon`
- Filament panel provider: `app/Providers/Filament/AdminPanelProvider.php`
- PRD docs: `doc/PRD/` (RAG-friendly, pyramid structure)
- Testing strategy: `doc/design/02-testing-strategy.md`
- Skills directory: `.ai/skills/`
- OpenCode config: `opencode.json`

## 铁律（不可违反）

### 编码质量规则

1. **代码变更后必须验证**：每次修改代码后，立即执行：
   - 语法检查: `docker compose exec app php -l {file}`
   - 代码风格: `docker compose exec app ./vendor/bin/pint`
   - 静态分析: `docker compose exec app ./vendor/bin/phpstan analyse`
   - 测试运行: `docker compose exec app ./vendor/bin/pest --compact`

2. **报错处理流程**：如果检查/测试失败：
   - 先向用户说明**失败原因**
   - 提供**改进方案**
   - 等待用户确认后再修复
   - 修复后重新执行检查直至通过

3. **禁止未经检查提交**：任何代码变更在未经以下检查前不得提交：
   - `./vendor/bin/pint --test` 通过
   - `./vendor/bin/phpstan analyse` 通过
   - `./vendor/bin/pest --compact` 通过

4. **格式规范强制**：
   - 所有 PHP 文件必须包含 `declare(strict_types=1);`
   - 使用 4 空格缩进
   - 使用短数组语法 `[]`
   - import 语句按字母排序
   - 类成员顺序: trait → constant → property → constructor → method

5. **修改范围限定**：
   - **只改需要改的**：不要"顺手"重构不相关的代码
   - **保持已有功能不退化**：修改某个功能前，先确认该功能当前是否正常；修改后必须验证原有功能未被破坏
   - **数据操作前先备份**：涉及数据库结构或数据变更的操作，先备份再执行
   - **缓存一致性**：修改数据库数据后，必须清除相关的 Redis/应用缓存

### 自我进化规则

1. **Bug 反思**：每次修复 Bug 后，分析根因并写入检查清单
2. **模式识别**：识别重复出现的错误模式，建立预防机制
3. **规则沉淀**：将修复经验转化为可执行的检查项

### 已知陷阱检查清单

- [ ] Filament 方法调用位置：defaultSort/Table, badge/Column
- [ ] 枚举 pluck：用静态数组，不用动态 pluck
- [ ] 列表页 action：不要加 header DeleteAction
- [ ] 复制文件：必须更新 namespace 和 use 语句
- [ ] 文件语法：修改后立即执行 `php -l` 检查
- [ ] `'hashed'` cast：Admin/Customer 模型有此 cast，创建用户时直接赋明文密码，不要用 bcrypt/Hash::make
- [ ] serialize() 不可用于含 PDO 连接的对象：用 toArray() + DB::select() 替代
- [ ] 地址快照导入后：必须重新 UPDATE parent_id 建立层级关系
- [ ] Blade 模板中 Collection 方法（->isEmpty()）在纯数组上报错：改用 empty()
- [ ] getViewData() 返回的 Eloquent Collection 无法被 Livewire 序列化：改用 public 属性 + 纯数组

### 页面功能测试规则

1. **新增 Filament 页面后必须测试**：
   - 列表页：表格显示、筛选、排序、分页
   - 详情页：Infolist 数据显示、关联数据
   - 创建页：表单验证、数据保存
   - 编辑页：数据回显、更新保存
   - 删除：确认弹窗、数据删除

2. **测试方法**：
   - 通过 curl 测试页面可访问性（302 = 正常跳转）
   - 检查路由注册是否正确
   - 检查模型关系是否定义
   - 检查 Infolist 字段是否正确引用模型属性

3. **常见错误预防**：
   - 图标名称必须有效（heroicon-o-xxx）
   - Infolist 字段必须对应模型真实属性或关系
   - 关系方法必须正确关联
   - 删除操作需要记录级联

4. **数据完整性检查**：
   - 创建数据后检查关联字段是否正确填充
   - 测试数据必须通过 HTTP 请求创建（有完整上下文）
   - 禁止直接插入数据库跳过中间件
