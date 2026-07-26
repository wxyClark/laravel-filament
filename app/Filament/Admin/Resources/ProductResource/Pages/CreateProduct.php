<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Domains\Logging\Models\AdminAuditLog;
use App\Filament\Admin\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        AdminAuditLog::record(
            action: 'created',
            subject: $this->record,
            newValues: ['name' => $this->record->name],
            description: "创建商品: {$this->record->name}",
        );
    }
}
