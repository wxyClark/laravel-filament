<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
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
                \Filament\Forms\Components\Select::make('parent_id')
                    ->label('上级地址')
                    ->options(\App\Models\Address::whereNull('parent_id')->pluck('name', 'id')),
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('地址名称')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('code')
                    ->label('行政区划代码')
                    ->required()
                    ->maxLength(6)
                    ->pattern('/^\d{6}$/'),
                \Filament\Forms\Components\Select::make('level')
                    ->label('层级')
                    ->required()
                    ->options([
                        'province' => '省级',
                        'city' => '地级',
                        'district' => '县级',
                    ]),
                \Filament\Forms\Components\TextInput::make('level_num')
                    ->label('层级编号')
                    ->numeric()
                    ->default(2),
                \Filament\Forms\Components\TextInput::make('pinyin')
                    ->label('拼音'),
                \Filament\Forms\Components\TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
