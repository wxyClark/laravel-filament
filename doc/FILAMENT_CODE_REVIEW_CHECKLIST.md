# Laravel-Filament 规范检查清单

> 代码审查时使用，逐项核对

---

## 一、架构规范检查

- [ ] 项目遵循 DDD 目录结构（Domains / Infrastructure / Http）
- [ ] 业务逻辑在 Service 层，不在 Resource 或 Controller 中
- [ ] DTO 类使用 readonly 属性
- [ ] 枚举实现了 `label()` 和 `color()` 方法
- [ ] 领域层不依赖任何框架类

## 二、Filament 资源检查

### 文件命名
- [ ] Resource 文件命名为 `[Entity]Resource.php`
- [ ] 列表页命名为 `List[Entity]s.php`
- [ ] 创建页命名为 `Create[Entity].php`
- [ ] 编辑页命名为 `Edit[Entity].php`
- [ ] 详情页命名为 `View[Entity].php`

### 资源配置
- [ ] Resource 定义了 `form()` 方法
- [ ] Resource 定义了 `table()` 方法
- [ ] Resource 定义了 `getPermissionPrefixes()` 方法
- [ ] Resource 设置了 `navigationIcon`
- [ ] Resource 设置了 `navigationGroup`

### 表单配置
- [ ] 必填字段标注了 `->required()`
- [ ] 唯一字段标注了 `->unique(ignoreRecord: true)`
- [ ] 金额字段使用 `->money()`
- [ ] 日期选择器使用 `->native(false)`
- [ ] 富文本/备注使用 `->columnSpanFull()`
- [ ] 表单按逻辑分 Section 分组

### 表格配置
- [ ] 设置了 `defaultPaginatedRecordLimit`
- [ ] 设置了 `defaultSort`
- [ ] 状态列使用了 `->badge()`
- [ ] 删除操作使用了 `->requiresConfirmation()`
- [ ] 批量删除使用了 `->destructive()`
- [ ] 定义了 `bulkActions`
- [ ] 定义了 `filters`
- [ ] 定义了 `search` 字段

## 三、权限控制检查

- [ ] 定义了 Policy 类
- [ ] 权限命名使用 `动作::域` 格式
- [ ] Resource 中配置了数据级权限过滤
- [ ] 敏感操作有权限检查

## 四、数据库规范检查

- [ ] 金额字段使用 `DECIMAL(10, 2)`
- [ ] 核心业务表使用 `SoftDeletes`
- [ ] 外键使用 `constrained()`
- [ ] 查询频繁的字段建立了索引
- [ ] JSON 字段创建了适当索引

## 五、代码风格检查

- [ ] 运行 `./vendor/bin/pint --test` 通过
- [ ] 运行 `./vendor/bin/phpstan analyse` 通过
- [ ] 文件命名符合规范
- [ ] 没有未使用的 import
- [ ] 没有未使用的变量

## 六、测试检查

- [ ] 核心 Service 有单元测试
- [ ] Filament Resource 有功能测试
- [ ] 使用 Factory 生成测试数据
- [ ] 测试遵循 AAA 模式

## 七、安全检查

- [ ] 所有用户输入经过验证
- [ ] 使用 Policy 控制权限
- [ ] 敏感数据使用加密
- [ ] 没有硬编码的密钥

## 八、公共组件检查

- [ ] 被 3+ 资源复用的组件已提取为公共组件
- [ ] 公共组件放在 `Components/` 目录
- [ ] 公共组件有清晰的文档注释

---

**审查结果:**
- 通过: ___ / ___
- 不通过项: ___

**审查人:** ______________  **日期:** ______________
