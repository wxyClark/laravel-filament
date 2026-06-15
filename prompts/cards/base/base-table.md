# 表格通用卡片

> **卡片 ID**: `base-table`
> **优先级**: L0
> **依赖**: `base-naming`

---

## 表格列配置规则

| 数据类型 | 列类型 | 配置示例 |
|---------|--------|---------|
| 字符串 | `TextColumn` | `->searchable()->sortable()->weight('medium')` |
| 邮箱 | `TextColumn` | `->searchable()->copyable()->copyMessage('已复制')` |
| 金额 | `TextColumn` | `->money('CNY')` |
| 状态 | `TextColumn->badge()` | `->badge()->color(fn($s)=>match($s){...})` |
| 布尔 | `IconColumn` | `->boolean()->sortable()` |
| 日期 | `TextColumn->dateTime()` | `->dateTime('Y-m-d H:i')->sortable()` |
| 时间戳 | `TextColumn->dateTime()` | `->dateTimeRelative()` |
| 关联 | `TextColumn->relationship()` | `->relationship('relation', 'display_field')` |

## 表格模板

```php
public static function table(Table $table): Table
{
    return $table
        ->defaultPaginatedRecordLimit(20)
        ->defaultSort('created_at', 'desc')
        ->columns([
            TextColumn::make('{{primary_field}}')
                ->searchable()
                ->sortable()
                ->weight('medium'),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    '{{status_value_1}}' => 'success',
                    '{{status_value_2}}' => 'warning',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => Status::from($state)->label()),
            TextColumn::make('created_at')
                ->dateTime('Y-m-d H:i')
                ->sortable()
                ->toggleable(),
        ])
        ->actions([
            EditAction::make()->icon('heroicon-o-pencil'),
            DeleteAction::make()->icon('heroicon-o-trash')->color('danger')->requiresConfirmation(),
        ])
        ->bulkActions([
            BulkAction::make('delete')
                ->action('bulkDelete')
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->destructive(),
        ])
        ->filters([
            SelectFilter::make('status')->options(Status::class),
            TrashedFilter::make(),
        ])
        ->search([
            '{{search_field_1}}',
            '{{search_field_2}}',
        ]);
}
```

## 操作按钮规则

1. 编辑操作始终存在：`EditAction::make()`
2. 删除操作必须 `->requiresConfirmation()`
3. 删除操作添加 `->destructive()` 样式
4. 批量删除添加 `->deselectRecordsAfterCompletion()`
5. 可切换显示的列添加 `->toggleable()`
6. 敏感数据列添加 `->copyable()` 并设置 `->copyMessage()`
