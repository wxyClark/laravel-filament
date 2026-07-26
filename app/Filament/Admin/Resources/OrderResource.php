<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Infrastructure\Filament\Concerns\HasResourcePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    use HasResourcePermission;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = '订单管理';

    protected static ?int $navigationSort = 2;

    protected static function getPermissionPrefix(): string
    {
        return 'order';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('订单信息')
                    ->schema([
                        Forms\Components\TextInput::make('order_no')
                            ->label('订单号')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('订单状态')
                            ->options(fn (?Order $record): array => self::getStatusOptions($record))
                            ->required(),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('订单总额')
                            ->disabled()
                            ->prefix('¥'),

                        Forms\Components\TextInput::make('freight')
                            ->label('运费')
                            ->disabled()
                            ->prefix('¥'),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('支付时间')
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

                Tables\Columns\TextColumn::make('order_no')
                    ->label('订单号')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('用户ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->formatStateUsing(fn (string $state): string => OrderStatus::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'info',
                        'completed' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('订单总额')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('freight')
                    ->label('运费')
                    ->money('CNY'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('支付时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options(OrderStatus::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
                Infolists\Components\Section::make('订单信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_no')
                            ->label('订单号'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('状态')
                            ->formatStateUsing(fn (string $state): string => OrderStatus::from($state)->label())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'info',
                                'completed' => 'success',
                                'canceled' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('订单总额')
                            ->money('CNY'),

                        Infolists\Components\TextEntry::make('freight')
                            ->label('运费')
                            ->money('CNY'),

                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('支付时间')
                            ->dateTime('Y-m-d H:i'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('创建时间')
                            ->dateTime('Y-m-d H:i'),
                    ])->columns(2),

                Infolists\Components\Section::make('订单商品')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('订单项')
                            ->schema([
                                Infolists\Components\TextEntry::make('sku_id')
                                    ->label('SKU ID'),

                                Infolists\Components\TextEntry::make('qty')
                                    ->label('数量'),

                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('单价')
                                    ->money('CNY'),

                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('小计')
                                    ->money('CNY'),
                            ])->columns(4),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    private static function getStatusOptions(?Order $record): array
    {
        if (! $record instanceof Order) {
            return array_map(fn (OrderStatus $s): string => $s->label(), OrderStatus::cases());
        }

        $current = $record->statusEnum();
        $options = [];

        foreach (OrderStatus::cases() as $status) {
            if ($current->canTransitionTo($status)) {
                $options[$status->value] = $status->label();
            }
        }

        $options[$current->value] = $current->label();

        return $options;
    }
}
