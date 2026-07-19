<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Models\Address;
use App\Services\AddressService;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\Paginator;

class ViewAddressList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static string $view = 'filament.pages.address-list';

    protected static ?string $title = '地址信息浏览';

    protected static ?string $navigationGroup = '地址管理';

    protected static ?int $navigationSort = 0;

    public ?int $selectedProvinceId = null;

    public ?int $selectedCityId = null;

    public ?int $selectedDistrictId = null;

    public ?int $selectedTownshipId = null;

    public int $page = 1;

    public int $perPage = 25;

    public int $totalResults = 0;

    protected function getViewData(): array
    {
        $service = app(AddressService::class);

        $provinces = $service->getChildrenByParentId(null);
        $cities = $this->selectedProvinceId
            ? $service->getChildrenByParentId($this->selectedProvinceId)
            : collect();
        $districts = $this->selectedCityId
            ? $service->getChildrenByParentId($this->selectedCityId)
            : collect();
        $townships = $this->selectedDistrictId
            ? $service->getChildrenByParentId($this->selectedDistrictId)
            : collect();

        return [
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'townships' => $townships,
        ];
    }

    public function updatedSelectedProvinceId(): void
    {
        $this->selectedCityId = null;
        $this->selectedDistrictId = null;
        $this->selectedTownshipId = null;
        $this->page = 1;
    }

    public function updatedSelectedCityId(): void
    {
        $this->selectedDistrictId = null;
        $this->selectedTownshipId = null;
        $this->page = 1;
    }

    public function updatedSelectedDistrictId(): void
    {
        $this->selectedTownshipId = null;
        $this->page = 1;
    }

    public function updatedSelectedTownshipId(): void
    {
        $this->page = 1;
    }

    public function updatedPerPage(): void
    {
        $this->page = 1;
    }

    public function resetFilters(): void
    {
        $this->selectedProvinceId = null;
        $this->selectedCityId = null;
        $this->selectedDistrictId = null;
        $this->selectedTownshipId = null;
        $this->page = 1;
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function getFilteredAddresses(): Paginator
    {
        $query = Address::query()->with('parent');

        if ($this->selectedTownshipId) {
            $query->where('id', $this->selectedTownshipId);
        } elseif ($this->selectedDistrictId) {
            $query->where('parent_id', $this->selectedDistrictId);
        } elseif ($this->selectedCityId) {
            $query->where('parent_id', $this->selectedCityId);
        } elseif ($this->selectedProvinceId) {
            $query->where('parent_id', $this->selectedProvinceId);
        }

        $this->totalResults = (clone $query)->count();

        return $query->orderBy('id')
            ->simplePaginate($this->perPage, ['*'], 'page', $this->page);
    }
}
