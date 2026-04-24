# 上下文规范：Filament 3.x 最佳实践

## 用途说明
确保生成的后台管理界面符合 Filament 3.x 的最新语法和设计理念。

## 适用场景
- 创建 Filament Resource、Widget 或 Custom Action 时。

## 标准内容块
```markdown
## Filament 3.x 开发规范
1. **Schema 语法**：全面使用链式调用（如 `TextEntry::make()->sortable()`）。
2. **Infolist 应用**：详情页必须使用 `Infolist` 展示只读信息。
3. **性能优化**：表格列中涉及关联计数的，必须使用 `withCount` 避免 N+1。
4. **权限控制**：所有敏感操作（Actions）必须集成 `Gate::authorize()`。
```
