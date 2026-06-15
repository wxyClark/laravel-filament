<?php

namespace App\Infrastructure\Filament\Resources\AddressResource\Tables;

use App\Models\Address;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AddressTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginatedRecordLimit(50)
            ->defaultSort('level_num', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('地区名称')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('code')
                    ->label('行政区划代码')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level')
                    ->label('层级')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'country' => 'gray',
                        'province' => 'primary',
                        'city' => 'info',
                        'district' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'country' => '国家',
                        'province' => '省/直辖市',
                        'city' => '地级市',
                        'district' => '区/县',
                        default => $state,
                    }),

                TextColumn::make('parent.name')
                    ->label('上级地区')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('level_num')
                    ->label('层级深度')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('层级')
                    ->options([
                        'country' => '国家',
                        'province' => '省/直辖市',
                        'city' => '地级市',
                        'district' => '区/县',
                    ]),

                SelectFilter::make('parent_id')
                    ->label('上级地区')
                    ->options(Address::whereNull('parent_id')->orderBy('sort')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->icon('heroicon-o-pencil'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action('bulkDelete')
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->destructive(),
            ])
            ->search([
                'name',
                'code',
                'pinyin',
            ]);
    }
}
