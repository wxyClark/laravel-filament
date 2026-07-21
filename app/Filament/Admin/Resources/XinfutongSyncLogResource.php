<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\Xinfutong\Enums\SyncStatus;
use App\Domains\Xinfutong\Models\SyncLog;
use App\Filament\Admin\Resources\XinfutongSyncLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class XinfutongSyncLogResource extends Resource
{
    protected static ?string $model = SyncLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = '薪福通';

    protected static ?string $navigationLabel = '同步日志';

    protected static ?string $modelLabel = '同步日志';

    protected static ?string $pluralModelLabel = '同步日志';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sync_type')
                    ->label('同步类型')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'full' ? '全量同步' : '增量同步')
                    ->color(fn (string $state): string => $state === 'full' ? 'primary' : 'info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (SyncStatus $state): string => $state->label())
                    ->color(fn (SyncStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('records_fetched')
                    ->label('拉取数')
                    ->sortable(),

                Tables\Columns\TextColumn::make('records_created')
                    ->label('新增数')
                    ->sortable(),

                Tables\Columns\TextColumn::make('records_updated')
                    ->label('更新数')
                    ->sortable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('错误信息')
                    ->limit(50)
                    ->tooltip(fn (SyncLog $record): ?string => $record->error_message),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('开始时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('完成时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListXinfutongSyncLogs::route('/'),
        ];
    }
}
