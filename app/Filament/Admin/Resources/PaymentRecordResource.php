<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\Payment\Enums\PaymentGatewayType;
use App\Domains\Payment\Enums\PaymentType;
use App\Domains\Payment\Models\PaymentRecord;
use App\Filament\Admin\Resources\PaymentRecordResource\Pages;
use App\Infrastructure\Filament\Concerns\HasResourcePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentRecordResource extends Resource
{
    use HasResourcePermission;

    protected static ?string $model = PaymentRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = '订单管理';

    protected static ?int $navigationSort = 3;

    protected static function getPermissionPrefix(): string
    {
        return 'payment';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('支付记录')
                    ->schema([
                        Forms\Components\TextInput::make('user_id')
                            ->label('用户ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('order_id')
                            ->label('订单ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('type')
                            ->label('类型')
                            ->disabled(),

                        Forms\Components\TextInput::make('amount')
                            ->label('金额')
                            ->disabled()
                            ->prefix('¥'),

                        Forms\Components\TextInput::make('gateway')
                            ->label('支付网关')
                            ->disabled(),

                        Forms\Components\TextInput::make('status')
                            ->label('状态')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('用户ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('订单ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->formatStateUsing(fn (string $state): string => PaymentType::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pay' => 'info',
                        'recharge' => 'success',
                        'refund' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('金额')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('支付网关')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'balance' => PaymentGatewayType::BALANCE->label(),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options(PaymentType::class),

                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        'success' => '成功',
                        'pending' => '待处理',
                        'failed' => '失败',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('支付记录详情')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID'),

                        Infolists\Components\TextEntry::make('user_id')
                            ->label('用户ID'),

                        Infolists\Components\TextEntry::make('order_id')
                            ->label('订单ID'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('类型')
                            ->formatStateUsing(fn (string $state): string => PaymentType::from($state)->label())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pay' => 'info',
                                'recharge' => 'success',
                                'refund' => 'warning',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('amount')
                            ->label('金额')
                            ->money('CNY'),

                        Infolists\Components\TextEntry::make('gateway')
                            ->label('支付网关')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'balance' => PaymentGatewayType::BALANCE->label(),
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('status')
                            ->label('状态')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'success' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('创建时间')
                            ->dateTime('Y-m-d H:i'),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentRecords::route('/'),
            'view' => Pages\ViewPaymentRecord::route('/{record}'),
        ];
    }
}
