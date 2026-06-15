<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditAddress extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function form(Form $form): Form
    {
        return $this->mutateFormDataBeforeFillForm(function (array $data): array {
            $data['level'] = $data['level'] ?? 'province';
            return $data;
        });
    }

    public function mutateFormDataBeforeFillForm(array $data): array
    {
        $record = $this->record;
        $data['level'] = $record->level ?? 'province';
        return $data;
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        $data['level_num'] = match($data['level']) {
            'province' => 2,
            'city' => 3,
            'district' => 4,
            default => 2,
        };
        return $data;
    }
}
