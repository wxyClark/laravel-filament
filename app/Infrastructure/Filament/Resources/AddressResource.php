<?php

namespace App\Infrastructure\Filament\Resources;

use App\Infrastructure\Filament\Resources\AddressResource\Forms\AddressForm;
use App\Infrastructure\Filament\Resources\AddressResource\Pages;
use App\Infrastructure\Filament\Resources\AddressResource\Tables\AddressTable;
use App\Models\Address;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = '基础数据';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return AddressForm::form($form);
    }

    public static function table(Table $table): Table
    {
        return AddressTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            // 暂无关系管理器
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
