<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Jobs\ExportAddressJob;
use App\Models\Admin;
use App\Services\AddressService;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

    public ?string $downloadUrl = null;

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
     * 获取筛选条件（页面显示和导出共用）
     */
    public function getFilters(): array
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
        $filters = $this->getFilters();

        $query = $service->buildQuery($filters);
        $this->totalResults = (clone $query)->count();

        return $query->orderBy('id')
            ->simplePaginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * 导出 CSV（按页面筛选条件）
     */
    public function exportCsv(): void
    {
        $this->doExport('csv');
    }

    /**
     * 导出 Excel（按页面筛选条件）
     */
    public function exportExcel(): void
    {
        $this->doExport('xlsx');
    }

    protected function doExport(string $format): void
    {
        $filters = $this->getFilters();

        /** @var Admin $user */
        $user = Auth::guard('admin')->user();

        ExportAddressJob::dispatch(
            $filters,
            $format,
            $user->id
        );

        $this->exporting = true;
        $this->exportMessage = '导出任务已提交，正在处理中...';
        $this->downloadUrl = null;

        // Dispatch browser event to poll for completion
        $this->dispatch('pollExportStatus');
    }

    /**
     * 检查导出状态（由前端轮询调用）
     */
    public function checkExportStatus(): void
    {
        /** @var Admin $user */
        $user = Auth::guard('admin')->user();
        $notification = Cache::get("export_notification_{$user->id}");

        if (! $notification) {
            return;
        }

        if ($notification['type'] === 'export') {
            $this->exportMessage = $notification['message'];

            if ($notification['success']) {
                $this->exporting = false;
                $this->downloadUrl = $notification['file_path'] ?? null;
                Cache::forget("export_notification_{$user->id}");
            } elseif (! $notification['success']) {
                $this->exporting = false;
                Cache::forget("export_notification_{$user->id}");
            }
        }
    }

    /**
     * 下载导出文件
     */
    public function getDownloadUrl(): ?string
    {
        if (! $this->downloadUrl) {
            return null;
        }

        return route('admin.api.export.download', ['filePath' => $this->downloadUrl]);
    }
}
