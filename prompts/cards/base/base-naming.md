# 命名规范基础卡片

> **卡片 ID**: `base-naming`
> **优先级**: L0（最底层，被所有卡片依赖）
> **适用**: 所有 Filament 开发任务

---

## 文件与类命名

| 类型 | 规则 | 示例 |
|------|------|------|
| Resource 类 | `[Entity]Resource` | `OrderResource.php` |
| 列表页 | `List[Entity]s` | `ListOrders.php` |
| 创建页 | `Create[Entity]` | `CreateOrder.php` |
| 编辑页 | `Edit[Entity]` | `EditOrder.php` |
| 详情页 | `View[Entity]` | `ViewOrder.php` |
| 表单组件 | `[Entity]Form` | `OrderForm.php` |
| 表格组件 | `[Entity]Table` | `OrderTable.php` |
| 关系管理器 | `[Entity]RelationManager` | `OrderItemsRelationManager.php` |
| Service 类 | `[Domain][Entity]Service` | `OrderService.php` |
| DTO 类 | `[Domain][Entity][Action]Data` | `OrderCreateData.php` |
| 枚举类 | `[Entity]Status` / `[Entity]Type` | `OrderStatus.php`, `OrderType.php` |
| 事件类 | `[Entity][Action]` | `OrderCreated.php` |
| Policy 类 | `[Entity]Policy` | `OrderPolicy.php` |
| Widget 类 | `[Entity][Entity]Widget` | `OrderStatsWidget.php` |

## 目录命名

```
app/Infrastructure/Filament/Resources/[Domain]/[Entity]Resource/
├── [Entity]Resource.php          # 资源类
├── Pages/
│   ├── List[Entity]s.php
│   ├── Create[Entity].php
│   └── Edit[Entity].php
├── Forms/
│   └── [Entity]Form.php
└── Tables/
    └── [Entity]Table.php
```

## 方法命名

- 动词 + 名词：`createOrder()`, `updateStatus()`, `getCust`
