<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\ApiTesting\Models\ApiModule;
use App\Filament\Admin\Resources\ApiModuleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApiModuleResource extends Resource
{
    protected static ?string $model = ApiModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = '接口测试';

    protected static ?string $navigationLabel = '模块管理';

    protected static ?string $modelLabel = '接口模块';

    protected static ?string $pluralModelLabel = '接口模块';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('模块名称')
                    ->required()
                    ->maxLength(100),

                Forms\Components\Textarea::make('description')
                    ->label('描述')
                    ->rows(3),

                Forms\Components\TextInput::make('sort_order')
                    ->label('排序')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('icon')
                    ->label('图标')
                    ->placeholder('heroicon-o-folder'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('模块名称')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->limit(50),

                Tables\Columns\TextColumn::make('functions_count')
                    ->label('功能数')
                    ->counts('functions'),

                Tables\Columns\TextColumn::make('interfaces_count')
                    ->label('接口数')
                    ->counts('interfaces'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiModules::route('/'),
            'create' => Pages\CreateApiModule::route('/create'),
            'edit' => Pages\EditApiModule::route('/{record}/edit'),
        ];
    }
}
