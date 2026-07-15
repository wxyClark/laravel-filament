<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = '地址管理';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = '地址信息';

    protected static ?string $modelLabel = '地址';

    protected static ?string $pluralModelLabel = '地址信息';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('parent_id')
                    ->label('上级地址')
                    ->options(Address::whereNull('parent_id')->pluck('name', 'id'))
                    ->reactive(),
                TextInput::make('name')
                    ->label('地址名称')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('行政区划代码')
                    ->required()
                    ->maxLength(6)
                    ->pattern('/^\d{6}$/')
                    ->unique(Address::class, 'code', ignoreRecord: true),
                Select::make('level')
                    ->label('层级')
                    ->required()
                    ->options([
                        'province' => '省级',
                        'city' => '地级',
                        'district' => '县级',
                    ])
                    ->default('province'),
                TextInput::make('level_num')
                    ->label('层级编号')
                    ->numeric()
                    ->default(2),
                TextInput::make('pinyin')
                    ->label('拼音')
                    ->maxLength(255),
                TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('代码')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('层级')
                    ->badge()
                    ->colors([
                        'primary' => 'province',
                        'success' => 'city',
                        'warning' => 'district',
                    ]),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('上级地址')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort')
                    ->label('排序')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('层级')
                    ->options([
                        'province' => '省级',
                        'city' => '地级',
                        'district' => '县级',
                    ]),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('上级地址')
                    ->options(Address::whereNull('parent_id')->pluck('name', 'id')),
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
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
            'view' => Pages\ViewAddress::route('/{record}'),
        ];
    }
}
