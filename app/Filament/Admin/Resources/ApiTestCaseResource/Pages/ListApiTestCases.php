<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ApiTestCaseResource\Pages;

use App\Filament\Admin\Resources\ApiTestCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApiTestCases extends ListRecords
{
    protected static string $resource = ApiTestCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
