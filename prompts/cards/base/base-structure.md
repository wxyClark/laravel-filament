# 文件结构基础卡片

> **卡片 ID**: `base-structure`
> **优先级**: L0
> **适用**: 所有 Filament 开发任务

---

## 标准 DDD 目录结构

```
app/
├── Domains/[Domain]/
│   ├── Models/           # 领域模型
│   ├── Services/         # 领域服务
│   ├── Data/             # DTO
│   ├── Events/           # 领域事件
│   ├── Enums/            # 枚举
│   └── Repositories/     # 仓储接口
│
├── Infrastructure/
│   ├── Filament/
│   │   ├── Panels/
│   │   ├── Resources/[Domain]/[Entity]Resource/
│   │   ├── Widgets/
│   │   ├── Pages/
│   │   └── Plugins/
│   ├── Repositories/
│   │   └── Eloquent/
│   ├── Services/
│   └── Support/
│       ├── Traits/
│       └── DTO/
│
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
│
└── Providers/

database/
├── migrations/
├── seeders/
└── factories/
```

## 关键原则

1. **Domain 层不依赖任何框架类**
2. **Filament 层只做展示配置**
3. **业务逻辑全部在 Service 层**
4. **Repository 层只负责数据访问**
