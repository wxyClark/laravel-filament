# 基础配置卡片

> **卡片 ID**: `base-config`
> **优先级**: L0
> **依赖**: `base-naming`, `base-structure`

---

## Resource 基础配置模板

```php
<?php

namespace {{resource_path}};

use App\Models\{{model_class}};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Tables\Actions\{CreateAction, EditAction, DeleteAction, BulkAction};
use Filament\Tables\Filters\{SelectFilter, TrashedFilter};

class {{entity}}Resource extends Resource
{
    protected static ?string $model = {{model_path}}::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationGroup = '{{domain}}';
    
    protected static ?int $navigationSort = {{sort}};
    
    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([
            // 由 base-form 卡片注入
            {{form_schema}}
        ])->columns(2);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginatedRecordLimit(20)
            ->defaultSort('created_at', 'desc')
            ->columns([
                // 由 base-table 卡片注入
                {{table_columns}}
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action('bulkDelete')
                    ->requiresConfirmation()
                    ->destructive(),
            ])
            ->filters([
                // 由业务卡片注入筛选器
                {{table_filters}}
            ])
            ->search([
                {{search_fields}}
            ]);
    }
}
```

## 页面基类

```php
<?php

namespace {{resource_path}}\Pages;

use {{resource_path}}\{{entity}}Resource;
use Filament\Resources\Pages\ListRecords;

class List{{entity_plural}} extends ListRecords
{
    protected static string $resource = {{entity}}Resource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```
