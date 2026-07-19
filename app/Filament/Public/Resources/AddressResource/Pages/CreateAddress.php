<?php

namespace App\Filament\Public\Resources\AddressResource\Pages;

use App\Models\Address;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateAddress extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('parent_id')
                    ->label('上级地址')
                    ->options(Address::whereNull('parent_id')->pluck('name', 'id')),
                TextInput::make('name')
                    ->label('地址名称')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('行政区划代码')
                    ->required()
                    ->maxLength(6)
                    ->pattern('/^\d{6}$/'),
                Select::make('level')
                    ->label('层级')
                    ->required()
                    ->options([
                        'province' => '省级',
                        'city' => '地级',
                        'district' => '县级',
                    ]),
                TextInput::make('level_num')
                    ->label('层级编号')
                    ->numeric()
                    ->default(2),
                TextInput::make('pinyin')
                    ->label('拼音'),
                TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
