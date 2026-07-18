<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\ApiTesting\Enums\AuthType;
use App\Domains\ApiTesting\Models\ApiEnvironment;
use App\Filament\Admin\Resources\ApiEnvironmentResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApiEnvironmentResource extends Resource
{
    protected static ?string $model = ApiEnvironment::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = '接口测试';

    protected static ?string $navigationLabel = '环境管理';

    protected static ?string $modelLabel = '测试环境';

    protected static ?string $pluralModelLabel = '测试环境';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本信息')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('环境名称')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('base_url')
                            ->label('Base URL')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('http://localhost:8080'),

                        Forms\Components\Select::make('auth_type')
                            ->label('认证方式')
                            ->options(collect(AuthType::cases())->pluck('label', 'value')->toArray())
                            ->default('none')
                            ->required(),

                        Forms\Components\Toggle::make('is_default')
                            ->label('默认环境')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('认证配置')
                    ->schema([
                        Forms\Components\KeyValue::make('auth_config')
                            ->label('认证参数')
                            ->helperText('JWT: token_url, username, password, token_path'),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('默认 Headers')
                    ->schema([
                        Forms\Components\KeyValue::make('headers')
                            ->label('Headers')
                            ->helperText('每个请求都会携带这些 Headers'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('环境名称')
                    ->searchable(),

                Tables\Columns\TextColumn::make('base_url')
                    ->label('Base URL')
                    ->limit(40),

                Tables\Columns\TextColumn::make('auth_type')
                    ->label('认证方式')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('默认')
                    ->boolean(),

                Tables\Columns\TextColumn::make('test_cases_count')
                    ->label('用例数')
                    ->counts('testCases'),

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
            'index' => Pages\ListApiEnvironments::route('/'),
            'create' => Pages\CreateApiEnvironment::route('/create'),
            'edit' => Pages\EditApiEnvironment::route('/{record}/edit'),
        ];
    }
}
