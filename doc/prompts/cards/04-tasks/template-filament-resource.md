# 任务模板：Filament 资源页面 (Filament Resource)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
规范 Filament 后台管理页面的创建过程，确保 Schema 语法和权限集成正确。

## 适用场景
- 创建 CRUD 管理页面
- 实现数据列表、表单、详情页

## 标准内容块
```markdown
# 任务：创建 {Model} Filament Resource

## L3: 角色设定
### Filament UI 设计师 (FilamentUIDesigner)
专注 Filament 3.x 最佳实践和用户体验。

## 要求
1. **Schema 语法**：全面使用链式调用
2. **Infolist 应用**：详情页必须使用 Infolist
3. **性能优化**：关联计数使用 `withCount` 避免 N+1
4. **权限控制**：敏感操作必须集成 `Gate::authorize()`

## 🎯 设计方案（必须解释）

### 1. 页面结构
| 页面 | 职责 | 核心字段 |
|------|------|---------|
| Table | 列表展示 | |
| Form | 数据编辑 | |
| Infolist | 详情展示 | |

### 2. 字段设计
| 字段名 | 类型 | 列表 | 表单 | 详情 | 原因 |
|--------|------|------|------|------|------|
| | | | | | |

### 3. 筛选器设计
| 筛选器 | 类型 | 字段 | 设计原因 |
|--------|------|------|---------|
| | | | |

### 4. 操作设计
| 操作 | 类型 | 权限 | 设计原因 |
|------|------|------|---------|
| | | | |

## 💻 代码实现
```php
<?php
declare(strict_types=1);

namespace App\Filament\Resources;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Sections::make([
                Section::make('基本信息')->schema([
                    TextInput::make('name')->required()->maxLength(255),
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
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('price')->money('cny')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([DatePicker::make('from'), DatePicker::make('until')]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action('bulkDelete')
                    ->danger()
                    ->requiresConfirmation(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Sections::make([
                Section::make('基本信息')->schema([
                    TextEntry::make('name'),
                    TextEntry::make('category.name'),
                    TextEntry::make('price')->money('cny'),
                ]),
            ]),
        ]);
    }
}
```

## L5: 验收标准
- [ ] Table 使用 withCount 避免 N+1
- [ ] Form 有分组 Layout
- [ ] 筛选器可用
- [ ] 删除操作有确认对话框
- [ ] Infolist 展示只读信息
- [ ] 权限校验集成
```
```
