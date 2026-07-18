<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Models\Address;
use App\Services\AddressService;
use Filament\Pages\Page;

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

    public bool $showDetailModal = false;

    public ?Address $detailAddress = null;

    public $parentChain = [];

    public int $childCount = 0;

    public int $totalChildCount = 0;

    public $children = [];

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

    public function getFilteredAddresses(): \Illuminate\Contracts\Pagination\Paginator
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

    public function viewDetail(int $id): void
    {
        $address = Address::find($id);

        if (! $address) {
            return;
        }

        $this->detailAddress = $address;

        // 获取上级地址链
        $this->parentChain = $this->getParentChain($address);

        // 获取直接下级数量
        $this->childCount = Address::where('parent_id', $address->id)->count();

        // 获取全部下级数量
        $this->totalChildCount = $this->getTotalChildCount($address->id);

        // 获取直接下级列表
        $this->children = Address::where('parent_id', $address->id)
            ->orderBy('sort')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailAddress = null;
    }

    protected function getParentChain(Address $address): array
    {
        $chain = [];
        $current = $address->parent;

        while ($current) {
            $chain[] = $current;
            $current = $current->parent;
        }

        return array_reverse($chain);
    }

    protected function getTotalChildCount(int $parentId): int
    {
        $count = 0;
        $children = Address::where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $count++;
            $count += $this->getTotalChildCount($childId);
        }

        return $count;
    }
}
