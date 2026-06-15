<?php

namespace App\Infrastructure\Filament\Resources\AddressResource\Forms;

use App\Models\Address;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;

class AddressForm
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('parent_id')
                    ->label('上级地区')
                    ->options(Address::whereNull('parent_id')->orderBy('sort')->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->disableOptionsWhenSelectedInSiblingSelects([
                        'children',
                    ])
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set, $record) => $set('children', null))
                    ->placeholder('选择上级地区（可选）'),

                TextInput::make('name')
                    ->label('地区名称')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get, $state) => $set('code', static::generateCodeFromName($state))),

                TextInput::make('code')
                    ->label('行政区划代码')
                    ->required()
                    ->maxLength(60)
                    ->helperText('如: 110000 (北京市)'),

                Select::make('level')
                    ->label('层级')
                    ->options([
                        'country' => '国家',
                        'province' => '省/直辖市',
                        'city' => '地级市',
                        'district' => '区/县',
                    ])
                    ->required()
                    ->default('province')
                    ->reactive(),

                TextInput::make('level_num')
                    ->label('层级深度')
                    ->integer()
                    ->default(1)
                    ->readOnly(),

                TextInput::make('pinyin')
                    ->label('拼音')
                    ->maxLength(100)
                    ->helperText('如: beijing'),

                Textarea::make('merge_path')
                    ->label('合并路径')
                    ->rows(3)
                    ->helperText('JSON格式: ["国家","省","市"]')
                    ->columnSpanFull(),

                TextInput::make('sort')
                    ->label('排序')
                    ->integer()
                    ->default(0),
            ])
            ->columns(2);
    }

    protected static function generateCodeFromName(string $name): string
    {
        return mb_strtolower(str_replace(' ', '', $name));
    }
}
