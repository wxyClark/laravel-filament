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

### TDD Workflow (7 steps)

1. **需求分析** → Read PRD docs in `doc/PRD/{module}/`
2. **架构设计** → Plan files per DDD layer
3. **数据库设计** → Create migration (`make:migration`)
4. **先写测试** → Red phase (Pest tests)
5. **实现代码** → Green phase (minimal code to pass)
6. **重构优化** → Refactor phase (Pint + PHPStan)
7. **联调验证** → End-to-end verification

### Skills Directory

| Skill | Purpose |
|-------|---------|
| `tdd-workflow` | Complete AI-assisted TDD flow |
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

## References

- CI pipeline: `.github/workflows/ci.yml`
- Code style rules: `.php-cs-fixer.php`
- PHPStan config: `phpstan.neon`
- Filament panel provider: `app/Providers/Filament/AdminPanelProvider.php`
- PRD docs: `doc/PRD/` (RAG-friendly, pyramid structure)
- Testing strategy: `doc/design/02-testing-strategy.md`
- Skills directory: `.ai/skills/`
- OpenCode config: `opencode.json`
