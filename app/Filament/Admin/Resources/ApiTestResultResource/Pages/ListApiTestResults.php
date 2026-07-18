<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ApiTestResultResource\Pages;

use App\Filament\Admin\Resources\ApiTestResultResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListApiTestResults extends ListRecords
{
    protected static string $resource = ApiTestResultResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('全部'),
            'passed' => Tab::make('通过')
                ->query(fn ($query) => $query->where('status', 'pass')),
            'failed' => Tab::make('失败')
                ->query(fn ($query) => $query->where('status', 'fail')),
            'errors' => Tab::make('错误')
                ->query(fn ($query) => $query->where('status', 'error')),
        ];
    }
}
