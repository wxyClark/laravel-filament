<?php

namespace App\Infrastructure\Filament\Resources\AddressResource\Pages;

use App\Infrastructure\Filament\Resources\AddressResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
