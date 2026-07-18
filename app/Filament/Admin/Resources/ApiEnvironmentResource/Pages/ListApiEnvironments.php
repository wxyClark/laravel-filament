<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ApiEnvironmentResource\Pages;

use App\Filament\Admin\Resources\ApiEnvironmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApiEnvironments extends ListRecords
{
    protected static string $resource = ApiEnvironmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
