<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CustomerResource\Pages;

use App\Domains\Logging\Models\AdminAuditLog;
use App\Filament\Admin\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function afterCreate(): void
    {
        AdminAuditLog::record(
            action: 'created',
            subject: $this->record,
            newValues: ['name' => $this->record->name, 'email' => $this->record->email],
            description: "创建用户: {$this->record->name}",
        );
    }
}
