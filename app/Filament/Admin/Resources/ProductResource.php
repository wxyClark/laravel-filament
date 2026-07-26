<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Product;
use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Infrastructure\Filament\Concerns\HasResourcePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use HasResourcePermission;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = '商品管理';

    protected static ?int $navigationSort = 1;

    protected static function getPermissionPrefix(): string
    {
        return 'product';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本信息')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('商品名称')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('商品类型')
                            ->options(ProductType::class)
                            ->required()
                            ->default(ProductType::ENTITY),

                        Forms\Components\Select::make('status')
                            ->label('状态')
                            ->options(ProductStatus::class)
                            ->required()
                            ->default(ProductStatus::ACTIVE),

                        Forms\Components\Textarea::make('description')
                            ->label('商品描述')
                            ->rows(3),

                        Forms\Components\FileUpload::make('image')
                            ->label('商品图片')
                            ->image()
                            ->directory('products')
                            ->maxSize(1024)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imagePreviewHeight('200')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('商品名称')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('图片')
                    ->circular()
                    ->defaultImageUrl(asset('favicon.ico')),

                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->formatStateUsing(fn (string $state): string => ProductType::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => ProductType::from($state)->color()),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->formatStateUsing(fn (string $state): string => ProductStatus::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match (ProductStatus::from($state)) {
                        ProductStatus::ACTIVE => 'success',
                        ProductStatus::INACTIVE => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options(ProductStatus::class),

                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options(ProductType::class),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
