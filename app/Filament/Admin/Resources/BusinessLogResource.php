<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\Logging\Enums\LogLevel;
use App\Domains\Logging\Models\BusinessLog;
use App\Filament\Admin\Resources\BusinessLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BusinessLogResource extends Resource
{
    protected static ?string $model = BusinessLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = '系统管理';

    protected static ?string $navigationLabel = '业务日志';

    protected static ?string $modelLabel = '业务日志';

    protected static ?string $pluralModelLabel = '业务日志';

    protected static ?int $navigationSort = 101;

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

                Tables\Columns\TextColumn::make('request_id')
                    ->label('请求 ID')
                    ->limit(8)
                    ->copyable()
                    ->copyMessage('已复制')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('level')
                    ->label('级别')
                    ->badge()
                    ->color(fn (LogLevel $state): string => $state->color()),

                Tables\Columns\TextColumn::make('channel')
                    ->label('通道')
                    ->badge(),

                Tables\Columns\TextColumn::make('message')
                    ->label('消息')
                    ->limit(50)
                    ->tooltip(fn (BusinessLog $record): string => $record->message),

                Tables\Columns\TextColumn::make('file')
                    ->label('文件')
                    ->limit(30)
                    ->placeholder('-')
                    ->tooltip(fn (BusinessLog $record): string => $record->file ? $record->file.':'.$record->line : ''),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('日志级别')
                    ->options([
                        'debug' => '调试',
                        'info' => '信息',
                        'warning' => '警告',
                        'error' => '错误',
                        'critical' => '严重',
                    ]),

                Tables\Filters\Filter::make('has_request_id')
                    ->label('有关联请求')
                    ->query(fn ($query) => $query->whereNotNull('request_id')),

                Tables\Filters\Filter::make('created_at')
                    ->label('时间范围')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('开始时间')
                            ->placeholder('开始时间'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('结束时间')
                            ->placeholder('结束时间'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when($data['created_from'], fn ($q, $date) => $q->where('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->where('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('基本信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID'),

                        Infolists\Components\TextEntry::make('request_id')
                            ->label('请求 ID')
                            ->copyable()
                            ->placeholder('无关联请求'),

                        Infolists\Components\TextEntry::make('level')
                            ->label('级别')
                            ->badge(),

                        Infolists\Components\TextEntry::make('channel')
                            ->label('通道')
                            ->badge(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('时间')
                            ->dateTime('Y-m-d H:i:s'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('日志内容')
                    ->schema([
                        Infolists\Components\TextEntry::make('message')
                            ->label('消息')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('context')
                            ->label('上下文')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-')
                            ->fontFamily('mono')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('extra')
                            ->label('额外数据')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('触发位置')
                    ->schema([
                        Infolists\Components\TextEntry::make('file')
                            ->label('文件')
                            ->fontFamily('mono'),

                        Infolists\Components\TextEntry::make('line')
                            ->label('行号'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('调用堆栈')
                    ->schema([
                        Infolists\Components\TextEntry::make('trace')
                            ->label('堆栈')
                            ->formatStateUsing(fn ($state) => $state ? Str::limit($state, 2000) : '-')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessLogs::route('/'),
            'view' => Pages\ViewBusinessLog::route('/{record}'),
        ];
    }
}
