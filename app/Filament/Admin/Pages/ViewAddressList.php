<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class ViewAddressList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static string $view = 'filament.pages.address-list';

    protected static ?string $title = '地址信息浏览';

    protected static ?string $navigationGroup = '地址管理';

    protected static ?int $navigationSort = 0;

    protected function getViewData(): array
    {
        return [
            'addressTree' => app(\App\Services\AddressService::class)->getAddressTree(),
        ];
    }
}
