<?php

namespace App\Filament\Public\Resources\AddressResource\Pages;

use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAddress extends ViewRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('地址详情')
                    ->schema([
                        TextEntry::make('name')->label('名称'),
                        TextEntry::make('code')->label('行政区划代码'),
                        TextEntry::make('level')->label('层级')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'province' => '省级',
                                'city' => '地级',
                                'district' => '县级',
                                default => $state,
                            }),
                        TextEntry::make('level_num')->label('层级编号'),
                        TextEntry::make('parent.name')->label('上级地址'),
                        TextEntry::make('pinyin')->label('拼音'),
                        TextEntry::make('sort')->label('排序'),
                        TextEntry::make('created_at')->label('创建时间')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')->label('更新时间')
                            ->dateTime('Y-m-d H:i:s'),
                    ])->columns(2),
            ]);
    }
}
