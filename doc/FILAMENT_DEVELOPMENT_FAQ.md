# Laravel-Filament 开发规范 FAQ

> 实施过程中常见问题解答

---

## 一、架构与目录

### Q1: 业务逻辑应该放在哪里？

**A:** 所有业务逻辑必须放在 `app/Domains/[Domain]/Services/` 下的 Service 类中。
- ❌ 不要在 Filament Resource 中写 `DB::transaction()`
- ❌ 不要在 Controller 中写业务规则
- ✅ 在 Service 中处理，在 Resource 中调用

### Q2: 何时使用 DTO？何时使用数组？

**A:**
- Service 之间传递数据使用 DTO（不可变、类型安全）
- 请求/响应转换可以使用数组
- DTO 文件放在 `app/Domains/[Domain]/Data/`

### Q3: Resource 目录按什么组织？

**A:** 按业务域分组：
```
app/Infrastructure/Filament/Resources/
├── User/AdminResource/
├── Product/ProductResource/
├── Trade/OrderResource/
└── Trade/PaymentResource/
```

---

## 二、Filament 开发

### Q4: 什么时候应该提取为公共组件？

**A:** 当同一个组件/表单字段/表格列在 **3 个及以上** 资源中使用时，必须提取。
- 状态切换开关 → `Components/Tenant/StatusToggle.php`
- 金额输入 → `Components/Common/MoneyInput.php`
- 关联选择 → `Components/Common/RelationshipSelect.php`

### Q5: 如何为 Resource 添加筛选器？

**A:** 在 `table()` 方法中添加：
```php
->filters([
    SelectFilter::make('status')->options(Status::class),
    \Filament\Tables\Filters\TrashedFilter::make(),
])
```

### Q6: 如何处理多对多关系的编辑？

**A:** 使用 `RelationManagers`：
```php
// 在 Resource 中
public static function getRelations(): array
{
    return [
        \Filament\Relations\RelationManagers\TagsRelationManager::class,
    ];
}
```

### Q7: 如何自定义 Filament 登录页面？

**A:** 在 `AdminPanelProvider` 中：
```php
->login(\App\Infrastructure\Filament\Pages\Auth\Login::class)
```

---

## 三、权限系统

### Q8: 权限命名有什么规范？

**A:** 使用 `动作::域` 格式：
- `view-any::orders` — 查看订单列表
- `view::orders` — 查看单个订单
- `create::orders` — 创建订单
- `update::orders` — 更新订单
- `delete::orders` — 删除订单

### Q9: 如何批量创建权限和角色？

**A:** 在 Seeder 中：
```php
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-any::orders', 'view::orders', 'create::orders',
            'update::orders', 'delete::orders',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
```

---

## 四、枚举使用

### Q10: 枚举必须实现哪些方法？

**A:** 必须实现 `label()` 和 `color()` 方法：
```php
enum OrderStatus: string
{
    case PENDING = 'pending';
    
    public function label(): string { return '待付款'; }
    public function color(): string { return 'warning'; }
}
```

---

## 五、测试

### Q11: Filament 测试怎么写？

**A:** 使用 Livewire 测试：
```php
test('admin can list orders', function () {
    $admin = Admin::factory()->create();
    $orders = Order::factory()->count(5)->create();
    
    Livewire::test(\App\Infrastructure\Filament\Resources\OrderResource\Pages\ListOrders::class)
        ->loginAs($admin)
        ->assertCanSeeTableRecords($orders);
});
```

---

## 六、工具链

### Q12: Pint 和 PHP-CS-Fixer 怎么选？

**A:** 本项目使用 **Pint**（Laravel 官方推荐），PHP-CS-Fixer 作为可选补充。

### Q13: PHPStan level 应该选多少？

**A:**
- 新项目: 从 level 5 开始，逐步提升到 max
- 维护项目: 设置 baseline，从 baseline 上方的 level 开始

### Q14: 如何跳过某些文件的 PHPStan 检查？

**A:** 在 `phpstan.neon` 的 `exclude_paths` 中添加：
```yaml
exclude_paths:
    - vendor/
    - storage/
    - bootstrap/cache/
    - routes/*.php  # 路由文件通常不需要检查
```

---

## 七、数据库

### Q15: 金额字段用什么类型？

**A:** 必须使用 `DECIMAL(10, 2)`，禁止使用 `FLOAT/DOUBLE`。

### Q16: 哪些表需要软删除？

**A:** 核心业务表必须开启软删除：
- 订单表 (`orders`)
- 商品表 (`products`)
- 分类表 (`categories`)
- 用户表 (`admins`, `customers`)

---

## 八、提示词卡片

### Q17: 如何使用提示词卡片系统？

**A:** 
1. 读取 `prompts/cards/card-system-design.md` 了解系统架构
2. 根据需要组合基础卡片（L0）和资源卡片（L1）
3. 使用业务卡片（L2）获取领域特定配置
4. 直接引用组合卡片（L3）获得完整实现

### Q18: 如何添加新的业务卡片？

**A:** 
1. 在 `prompts/cards/biz/` 下创建新文件
2. 遵循现有业务卡片的格式（核心实体、核心 Service、关键字段、Filament 资源）
3. 在 `card-system-design.md` 的卡片分类表中注册

---

*持续更新中，如有新问题请提交 Issue。*
