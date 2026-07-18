<?php

declare(strict_types=1);

namespace App\Infrastructure\Filament\Resources\BusinessLogResource\Pages;

use App\Infrastructure\Filament\Resources\BusinessLogResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListBusinessLogs extends ListRecords
{
    protected static string $resource = BusinessLogResource::class;

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
            'info' => Tab::make('信息')
                ->query(fn ($query) => $query->where('level', 'info')),
            'warning' => Tab::make('警告')
                ->query(fn ($query) => $query->where('level', 'warning')),
            'error' => Tab::make('错误')
                ->query(fn ($query) => $query->where('level', 'error')),
            'critical' => Tab::make('严重')
                ->query(fn ($query) => $query->where('level', 'critical')),
        ];
    }
}
