<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\ApiTesting\Enums\TestStatus;
use App\Domains\ApiTesting\Models\ApiTestResult;
use App\Filament\Admin\Resources\ApiTestResultResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApiTestResultResource extends Resource
{
    protected static ?string $model = ApiTestResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = '接口测试';

    protected static ?string $navigationLabel = '测试结果';

    protected static ?string $modelLabel = '测试结果';

    protected static ?string $pluralModelLabel = '测试结果';

    protected static ?int $navigationSort = 5;

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

                Tables\Columns\TextColumn::make('testCase.name')
                    ->label('用例名称')
                    ->searchable(),

                Tables\Columns\TextColumn::make('interface.name')
                    ->label('接口'),

                Tables\Columns\TextColumn::make('environment.name')
                    ->label('环境'),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->color(fn (TestStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('request_method')
                    ->label('方法')
                    ->badge(),

                Tables\Columns\TextColumn::make('response_status')
                    ->label('状态码'),

                Tables\Columns\TextColumn::make('response_time')
                    ->label('耗时')
                    ->suffix('ms'),

                Tables\Columns\TextColumn::make('executed_at')
                    ->label('执行时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->defaultSort('desc'),
            ])
            ->defaultSort('executed_at', 'desc')
            ->modifyQueryUsing(function ($query) {
                if (request()->has('batch_id')) {
                    $batch = DB::table('api_test_batches')->find(request('batch_id'));
                    if ($batch) {
                        $testCaseIds = json_decode($batch->test_case_ids, true) ?? [];
                        $query->whereIn('test_case_id', $testCaseIds);
                    }
                }
            })
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        'pass' => '通过',
                        'fail' => '失败',
                        'error' => '错误',
                        'skip' => '跳过',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('时间范围')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('开始时间'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('结束时间'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when($data['created_from'], fn ($q, $date) => $q->where('executed_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->where('executed_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('基本信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('testCase.name')
                            ->label('用例名称'),

                        Infolists\Components\TextEntry::make('interface.name')
                            ->label('接口'),

                        Infolists\Components\TextEntry::make('environment.name')
                            ->label('环境'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('状态')
                            ->badge(),

                        Infolists\Components\TextEntry::make('executed_at')
                            ->label('执行时间')
                            ->dateTime('Y-m-d H:i:s'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('请求信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('request_method')
                            ->label('方法')
                            ->badge(),

                        Infolists\Components\TextEntry::make('request_url')
                            ->label('URL')
                            ->fontFamily('mono'),

                        Infolists\Components\TextEntry::make('request_headers')
                            ->label('Headers')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-')
                            ->fontFamily('mono')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('request_body')
                            ->label('Body')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('响应信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('response_status')
                            ->label('状态码')
                            ->badge(),

                        Infolists\Components\TextEntry::make('response_time')
                            ->label('响应时间')
                            ->suffix(' ms'),

                        Infolists\Components\TextEntry::make('response_body')
                            ->label('响应 Body')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('断言结果')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('assertion_results')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label('类型')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('path')
                                    ->label('路径')
                                    ->placeholder('-'),

                                Infolists\Components\TextEntry::make('expected')
                                    ->label('期望值'),

                                Infolists\Components\TextEntry::make('actual')
                                    ->label('实际值'),

                                Infolists\Components\IconEntry::make('passed')
                                    ->label('结果')
                                    ->boolean(),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('错误信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('错误')
                            ->placeholder('无错误')
                            ->color('danger')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiTestResults::route('/'),
            'view' => Pages\ViewApiTestResult::route('/{record}'),
        ];
    }
}
