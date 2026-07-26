<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\AfterSale\Enums\AfterSaleStatus;
use App\Domains\AfterSale\Enums\AfterSaleType;
use App\Domains\AfterSale\Models\AfterSale;
use App\Filament\Admin\Resources\AfterSaleResource\Pages;
use App\Infrastructure\Filament\Concerns\HasResourcePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AfterSaleResource extends Resource
{
    use HasResourcePermission;

    protected static ?string $model = AfterSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = '订单管理';

    protected static ?int $navigationSort = 5;

    protected static function getPermissionPrefix(): string
    {
        return 'after-sale';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('售后信息')
                    ->schema([
                        Forms\Components\TextInput::make('order_id')
                            ->label('订单ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('user_id')
                            ->label('用户ID')
                            ->disabled(),

                        Forms\Components\Select::make('type')
                            ->label('售后类型')
                            ->options(AfterSaleType::class)
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('售后状态')
                            ->options([
                                AfterSaleStatus::PENDING->value => AfterSaleStatus::PENDING->label(),
                                AfterSaleStatus::COMPLETED->value => AfterSaleStatus::COMPLETED->label(),
                                AfterSaleStatus::REJECTED->value => AfterSaleStatus::REJECTED->label(),
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('refund_amount')
                            ->label('退款金额')
                            ->disabled()
                            ->prefix('¥'),
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

                Tables\Columns\TextColumn::make('order_id')
                    ->label('订单ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('用户ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->formatStateUsing(fn (string $state): string => AfterSaleType::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'refund' => 'warning',
                        'return' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->formatStateUsing(fn (string $state): string => AfterSaleStatus::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('退款金额')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options(AfterSaleType::class),

                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options(AfterSaleStatus::class),
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
                Infolists\Components\Section::make('售后详情')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID'),

                        Infolists\Components\TextEntry::make('order_id')
                            ->label('订单ID'),

                        Infolists\Components\TextEntry::make('user_id')
                            ->label('用户ID'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('类型')
                            ->formatStateUsing(fn (string $state): string => AfterSaleType::from($state)->label())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'refund' => 'warning',
                                'return' => 'info',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('status')
                            ->label('状态')
                            ->formatStateUsing(fn (string $state): string => AfterSaleStatus::from($state)->label())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('refund_amount')
                            ->label('退款金额')
                            ->money('CNY'),

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
            'index' => Pages\ListAfterSales::route('/'),
            'create' => Pages\CreateAfterSale::route('/create'),
            'edit' => Pages\EditAfterSale::route('/{record}/edit'),
            'view' => Pages\ViewAfterSale::route('/{record}'),
        ];
    }
}
