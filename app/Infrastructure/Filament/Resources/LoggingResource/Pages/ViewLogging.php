<?php

declare(strict_types=1);

namespace App\Infrastructure\Filament\Resources\LoggingResource\Pages;

use App\Infrastructure\Filament\Resources\LoggingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLogging extends ViewRecord
{
    protected static string $resource = LoggingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
