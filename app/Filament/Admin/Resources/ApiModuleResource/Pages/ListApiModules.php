<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ApiModuleResource\Pages;

use App\Filament\Admin\Resources\ApiModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApiModules extends ListRecords
{
    protected static string $resource = ApiModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
