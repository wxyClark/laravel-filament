<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Domains\Fulfillment\Enums\FulfillmentStatus;
use App\Domains\Fulfillment\Enums\FulfillmentType;
use App\Domains\Fulfillment\Models\Fulfillment;
use App\Filament\Admin\Resources\FulfillmentResource\Pages;
use App\Infrastructure\Filament\Concerns\HasResourcePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FulfillmentResource extends Resource
{
    use HasResourcePermission;

    protected static ?string $model = Fulfillment::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = '订单管理';

    protected static ?int $navigationSort = 4;

    protected static function getPermissionPrefix(): string
    {
        return 'fulfillment';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('履约信息')
                    ->schema([
                        Forms\Components\TextInput::make('order_id')
                            ->label('订单ID')
                            ->disabled(),

                        Forms\Components\Select::make('type')
                            ->label('履约类型')
                            ->options(FulfillmentType::class)
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('履约状态')
                            ->options(FulfillmentStatus::class)
                            ->required(),

                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('交付时间')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('物流信息')
                    ->schema([
                        Forms\Components\TextInput::make('shipment.carrier')
                            ->label('承运商')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('shipment.tracking_no')
                            ->label('运单号')
                            ->maxLength(255),
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

                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->formatStateUsing(fn (string $state): string => FulfillmentType::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entity' => 'info',
                        'virtual' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->formatStateUsing(fn (string $state): string => FulfillmentStatus::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'delivered' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('交付时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options(FulfillmentType::class),

                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options(FulfillmentStatus::class),
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
                Infolists\Components\Section::make('履约信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID'),

                        Infolists\Components\TextEntry::make('order_id')
                            ->label('订单ID'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('类型')
                            ->formatStateUsing(fn (string $state): string => FulfillmentType::from($state)->label())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'entity' => 'info',
                                'virtual' => 'success',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('status')
                            ->label('状态')
                            ->formatStateUsing(fn (string $state): string => FulfillmentStatus::from($state)->label())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'delivered' => 'success',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('delivered_at')
                            ->label('交付时间')
                            ->dateTime('Y-m-d H:i'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('创建时间')
                            ->dateTime('Y-m-d H:i'),
                    ])->columns(2),

                Infolists\Components\Section::make('物流信息')
                    ->schema([
                        Infolists\Components\TextEntry::make('shipment.carrier')
                            ->label('承运商')
                            ->placeholder('暂无'),

                        Infolists\Components\TextEntry::make('shipment.tracking_no')
                            ->label('运单号')
                            ->placeholder('暂无'),
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
            'index' => Pages\ListFulfillments::route('/'),
            'create' => Pages\CreateFulfillment::route('/create'),
            'edit' => Pages\EditFulfillment::route('/{record}/edit'),
            'view' => Pages\ViewFulfillment::route('/{record}'),
        ];
    }
}
