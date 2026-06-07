# 上下文规范：Filament 3.x 最佳实践

> **版本**: v3.0 | **层級**: L2 | **最后更新**: 2026-06-07

## 用途说明
确保生成的后台管理界面符合 Filament 3.x 的最新语法和设计理念。

## 适用场景
- 创建 Filament Resource、Widget 或 Custom Action 时
- 构建 Filament Page、Table Column、Form Field 时

## 标准内容块
```markdown
## Filament 3.x 开发规范

### 强制要求
1. **Schema 语法**：全面使用链式调用（`TextEntry::make()->sortable()`）
2. **Infolist 应用**：详情页必须使用 Infolist 展示只读信息
3. **性能优化**：表格列中涉及关联计数的，必须使用 `withCount` 避免 N+1
4. **权限控制**：所有敏感操作（Actions）必须集成 `Gate::authorize()`

### Resource 模板
```php
public static function form(Form $form): Form
{
    return $form->schema([
        Sections::make([
            Section::make('基本信息')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),
        ]),
    ])->columns(2);
}

public static function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('name')->searchable()->sortable(),
        TextColumn::make('category.name')->sortable(),
        TextColumn::make('price')->money('cny')->sortable(),
        TextColumn::make('created_at')->dateTime()->sortable(),
    ])->filters([
        Filter::make('created_at')
            ->form([DatePicker::make('from'), DatePicker::make('until')])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when($data['from'], fn(Builder $q, $d): Builder => $q->whereDate('created_at', '>=', $d))
                    ->when($data['until'], fn(Builder $q, $d): Builder => $q->whereDate('created_at', '<=', $d));
            }),
    ])->actions([
        EditAction::make(),
        DeleteAction::make()->requiresConfirmation(),
    ])->bulkActions([
        BulkAction::make('delete')
            ->action('bulkDelete')
            ->danger()
            ->requiresConfirmation(),
    ]);
}
```

### 禁止做法
- ❌ 在 Resource 中直接写 Eloquent 查询（使用 `applyGlobalScopes()` 替代）
- ❌ 在表格中使用 `with()` 预加载（使用 `withCount` / `withSum`）
- ❌ 在 Action 中写业务逻辑（委托给 Service）
```
```
