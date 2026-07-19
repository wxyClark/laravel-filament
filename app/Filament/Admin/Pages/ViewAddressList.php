<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Jobs\ExportAddressJob;
use App\Models\Admin;
use App\Services\AddressService;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

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

    public bool $exporting = false;

    public string $exportMessage = '';

    // 导出筛选弹窗
    public bool $showExportModal = false;

    public ?string $exportLevel = null;

    public ?string $exportKeyword = null;

    public int $exportTotalCount = 0;

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

    /**
     * 获取页面显示的筛选条件
     */
    public function getPageFilters(): array
    {
        $filters = [];

        if ($this->selectedTownshipId) {
            $filters['parent_id'] = $this->selectedTownshipId;
        } elseif ($this->selectedDistrictId) {
            $filters['parent_id'] = $this->selectedDistrictId;
        } elseif ($this->selectedCityId) {
            $filters['parent_id'] = $this->selectedCityId;
        } elseif ($this->selectedProvinceId) {
            $filters['parent_id'] = $this->selectedProvinceId;
        }

        return $filters;
    }

    /**
     * 页面显示查询（使用共享查询逻辑）
     */
    public function getFilteredAddresses(): Paginator
    {
        $service = app(AddressService::class);
        $filters = $this->getPageFilters();

        $query = $service->buildQuery($filters);
        $this->totalResults = (clone $query)->count();

        return $query->orderBy('id')
            ->simplePaginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * 打开导出筛选弹窗
     */
    public function openExportModal(): void
    {
        $this->showExportModal = true;
        $this->exportLevel = null;
        $this->exportKeyword = null;
        $this->updateExportCount();
    }

    /**
     * 关闭导出筛选弹窗
     */
    public function closeExportModal(): void
    {
        $this->showExportModal = false;
    }

    /**
     * 导出筛选条件变化时更新计数
     */
    public function updatedExportLevel(): void
    {
        $this->updateExportCount();
    }

    public function updatedExportKeyword(): void
    {
        $this->updateExportCount();
    }

    protected function updateExportCount(): void
    {
        $filters = $this->getExportFilters();
        $service = app(AddressService::class);
        $this->exportTotalCount = $service->buildQuery($filters)->count();
    }

    /**
     * 获取导出筛选条件（页面筛选 + 导出弹窗筛选）
     */
    public function getExportFilters(): array
    {
        $filters = $this->getPageFilters();

        // 叠加导出弹窗的额外筛选
        if ($this->exportLevel) {
            $filters['level'] = $this->exportLevel;
        }

        if ($this->exportKeyword) {
            $filters['keyword'] = $this->exportKeyword;
        }

        return $filters;
    }

    /**
     * 导出 CSV
     */
    public function exportCsv(): void
    {
        $this->doExport('csv');
    }

    /**
     * 导出 Excel
     */
    public function exportExcel(): void
    {
        $this->doExport('xlsx');
    }

    protected function doExport(string $format): void
    {
        $filters = $this->getExportFilters();

        /** @var Admin $user */
        $user = Auth::guard('admin')->user();

        ExportAddressJob::dispatch(
            $filters,
            $format,
            $user->id
        );

        $this->showExportModal = false;
        $this->exporting = true;
        $this->exportMessage = '导出任务已提交，正在处理中...';
    }
}
