<x-filament-panels::page>
    <div class="space-y-4">
        {{-- 级联筛选区 --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-900">
            <div class="flex gap-4">
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">省份</label>
                    <select wire:model.live="selectedProvinceId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">全部</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">城市</label>
                    <select wire:model.live="selectedCityId" @disabled($cities->isEmpty()) class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50">
                        <option value="">全部</option>
                        @foreach($cities as $city)
                            <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">区县</label>
                    <select wire:model.live="selectedDistrictId" @disabled($districts->isEmpty()) class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50">
                        <option value="">全部</option>
                        @foreach($districts as $district)
                            <option value="{{ $district['id'] }}">{{ $district['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">街道</label>
                    <select wire:model.live="selectedTownshipId" @disabled($townships->isEmpty()) class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50">
                        <option value="">全部</option>
                        @foreach($townships as $township)
                            <option value="{{ $township['id'] }}">{{ $township['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1" style="width: 100px">
                    <label class="text-sm font-medium text-transparent">操作</label>
                    <button wire:click="resetFilters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        重置
                    </button>
                </div>
            </div>
        </div>

        {{-- 结果统计 --}}
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                共 <span class="font-semibold">{{ $totalResults }}</span> 条结果
            </span>
            <div class="flex items-center gap-3">
                {{-- 导出按钮 --}}
                <div class="flex items-center gap-2">
                    <button wire:click="openExportModal" @disabled($exporting) class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <x-heroicon-o-document-arrow-down class="h-4 w-4" />
                        导出数据
                    </button>
                </div>
                @if($exporting)
                    <div class="flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ $exportMessage }}
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">每页</label>
                    <select wire:model.live="perPage" class="rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label class="text-sm text-gray-600 dark:text-gray-400">条</label>
                </div>
            </div>
        </div>

        {{-- 数据表格 --}}
        <div class="w-full overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-900">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="w-16 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名称</th>
                        <th class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">代码</th>
                        <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">层级</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">上级</th>
                        <th class="w-48 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">拼音</th>
                        <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                    @forelse($this->getFilteredAddresses() as $addr)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $addr->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $addr->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">{{ $addr->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($addr->level === 'province')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">省级</span>
                                @elseif($addr->level === 'city')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">地级</span>
                                @elseif($addr->level === 'district')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">县级</span>
                                @elseif($addr->level === 'township')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">街道</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $addr->level }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $addr->parent?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $addr->pinyin ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('filament.admin.pages.address', ['id' => $addr->id]) }}" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 hover:underline">
                                    详情
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                暂无数据
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页导航 --}}
        @php
            $paginator = $this->getFilteredAddresses();
        @endphp
        @if($paginator->hasPages())
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    显示 {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} 条
                </div>
                <div class="flex items-center gap-2">
                    @if($paginator->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">← 上一页</span>
                    @else
                        <button wire:click="previousPage" class="px-3 py-2 text-sm text-gray-700 bg-white border rounded-lg hover:bg-gray-50">← 上一页</button>
                    @endif
                    <span class="px-3 py-2 text-sm text-gray-500">第 {{ $paginator->currentPage() }} 页</span>
                    @if($paginator->hasMorePages())
                        <button wire:click="nextPage" class="px-3 py-2 text-sm text-gray-700 bg-white border rounded-lg hover:bg-gray-50">下一页 →</button>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">下一页 →</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- 导出筛选弹窗 --}}
    @if($showExportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeExportModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    导出数据配置
                                </h3>
                                <div class="mt-4 space-y-4">
                                    {{-- 当前筛选条件 --}}
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">当前页面筛选条件</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            @if($totalResults > 0)
                                                匹配 <span class="font-semibold text-primary-600 dark:text-primary-400">{{ number_format($totalResults) }}</span> 条数据
                                            @else
                                                <span class="text-gray-500">无匹配数据</span>
                                            @endif
                                        </p>
                                    </div>

                                    {{-- 层级筛选 --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">层级筛选</label>
                                        <select wire:model.live="exportLevel" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                            <option value="">全部层级</option>
                                            <option value="province">省级</option>
                                            <option value="city">地级</option>
                                            <option value="district">县级</option>
                                            <option value="township">街道</option>
                                        </select>
                                    </div>

                                    {{-- 关键词搜索 --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">关键词搜索</label>
                                        <input type="text" wire:model.live="exportKeyword" placeholder="名称、编码、拼音" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    </div>

                                    {{-- 导出预览 --}}
                                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-blue-700 dark:text-blue-300">导出数据量</span>
                                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($exportTotalCount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button wire:click="exportCsv" @disabled($exporting || $exportTotalCount === 0) class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 transition-colors">
                            <x-heroicon-o-document-arrow-down class="h-4 w-4" />
                            导出 CSV
                        </button>
                        <button wire:click="exportExcel" @disabled($exporting || $exportTotalCount === 0) class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <x-heroicon-o-table-cells class="h-4 w-4" />
                            导出 Excel
                        </button>
                        <button type="button" wire:click="closeExportModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500 transition-colors">
                            取消
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
