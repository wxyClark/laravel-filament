<?php

declare(strict_types=1);

namespace App\Infrastructure\Filament\Resources\LoggingResource\Pages;

use App\Infrastructure\Filament\Resources\LoggingResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListLogging extends ListRecords
{
    protected static string $resource = LoggingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('全部'),
            'errors' => Tab::make('异常请求')
                ->query(fn ($query) => $query->where('response_status', '>=', 400)
                    ->orWhereNotNull('exception_class')),
            'slow' => Tab::make('慢请求')
                ->query(fn ($query) => $query->where('response_time', '>', 1000)),
        ];
    }
}
