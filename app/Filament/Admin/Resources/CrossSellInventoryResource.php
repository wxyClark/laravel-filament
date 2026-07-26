<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\CrossSell\Models\Inventory;
use App\Domains\CrossSell\Models\SKU;
use App\Domains\CrossSell\Models\Warehouse;
use App\Filament\Admin\Resources\CrossSellInventoryResource\Pages;
use App\Infrastructure\Filament\Concerns\HasResourcePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * 库存管理 Resource
 */
class CrossSellInventoryResource extends Resource
{
    use HasResourcePermission;

    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'CrossSell 管理';

    protected static ?string $navigationLabel = '库存管理';

    protected static ?string $modelLabel = '库存';

    protected static ?string $pluralModelLabel = '库存';

    protected static ?int $navigationSort = 5;

    protected static function getPermissionPrefix(): string
    {
        return 'cross-sell-inventory';
    }

    /**
     * 表单定义
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('库存信息')
                    ->schema([
                        Forms\Components\Select::make('sku_id')
                            ->label('SKU')
                            ->options(fn () => SKU::with('product')->get()->mapWithKeys(fn ($sku) => [$sku->id => "{$sku->product->title} - {$sku->sku_code}"]))
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('warehouse_id')
                            ->label('仓库')
                            ->options(Warehouse::pluck('name', 'id'))
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('库存数量')
                            ->numeric()
                            ->required()
                            ->default(0),

                        Forms\Components\TextInput::make('reserved_quantity')
                            ->label('预留数量')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('available_quantity')
                            ->label('可用数量')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('min_stock')
                            ->label('最低库存')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('max_stock')
                            ->label('最高库存')
                            ->numeric()
                            ->default(9999),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * 表格定义
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku.sku_code')
                    ->label('SKU编码')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku.product.title')
                    ->label('商品')
                    ->searchable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('仓库')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('库存数量')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reserved_quantity')
                    ->label('预留数量')
                    ->sortable(),

                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('可用数量')
                    ->sortable()
                    ->color(fn ($state) => $state <= 10 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('最低库存')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('仓库')
                    ->options(Warehouse::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * 信息列表定义
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('库存信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('sku.sku_code')
                            ->label('SKU编码'),

                        Infolists\Components\TextEntry::make('sku.product.title')
                            ->label('商品'),

                        Infolists\Components\TextEntry::make('warehouse.name')
                            ->label('仓库'),

                        Infolists\Components\TextEntry::make('quantity')
                            ->label('库存数量'),

                        Infolists\Components\TextEntry::make('reserved_quantity')
                            ->label('预留数量'),

                        Infolists\Components\TextEntry::make('available_quantity')
                            ->label('可用数量'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrossSellInventories::route('/'),
            'create' => Pages\CreateCrossSellInventory::route('/create'),
            'edit' => Pages\EditCrossSellInventory::route('/{record}/edit'),
            'view' => Pages\ViewCrossSellInventory::route('/{record}'),
        ];
    }
}
