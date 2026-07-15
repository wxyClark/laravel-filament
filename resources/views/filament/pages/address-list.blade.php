<x-filament-panels::page>
    <div class="space-y-4">
        {{-- 级联筛选区 --}}
        <div class="flex flex-wrap items-end gap-3 p-4 bg-white rounded-lg shadow dark:bg-gray-900">
            {{-- 省份 --}}
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">省份</label>
                <select
                    wire:model.live="selectedProvinceId"
                    class="w-80 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">全部</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 城市 --}}
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">城市</label>
                <select
                    wire:model.live="selectedCityId"
                    @disabled($cities->isEmpty())
                    class="w-80 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                >
                    <option value="">全部</option>
                    @foreach($cities as $city)
                        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 区县 --}}
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">区县</label>
                <select
                    wire:model.live="selectedDistrictId"
                    @disabled($districts->isEmpty())
                    class="w-80 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                >
                    <option value="">全部</option>
                    @foreach($districts as $district)
                        <option value="{{ $district['id'] }}">{{ $district['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 街道 --}}
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">街道</label>
                <select
                    wire:model.live="selectedTownshipId"
                    @disabled($townships->isEmpty())
                    class="w-80 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                >
                    <option value="">全部</option>
                    @foreach($townships as $township)
                        <option value="{{ $township['id'] }}">{{ $township['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 重置按钮 --}}
            <button
                wire:click="resetFilters"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
                重置
            </button>
        </div>

        {{-- 结果统计 + 每页条数 --}}
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                共 <span class="font-semibold">{{ $totalResults }}</span> 条结果
            </span>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 dark:text-gray-400">每页</label>
                <select
                    wire:model.live="perPage"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                >
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="text-sm text-gray-600 dark:text-gray-400">条</label>
            </div>
        </div>

        {{-- 数据表格 --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-900">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">名称</th>
                        <th class="px-4 py-3">代码</th>
                        <th class="px-4 py-3">层级</th>
                        <th class="px-4 py-3">上级</th>
                        <th class="px-4 py-3">拼音</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getFilteredAddresses() as $addr)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3">{{ $addr->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $addr->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $addr->code }}</td>
                            <td class="px-4 py-3">
                                @if($addr->level === 'province')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">省级</span>
                                @elseif($addr->level === 'city')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">地级</span>
                                @elseif($addr->level === 'district')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">县级</span>
                                @elseif($addr->level === 'township')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">街道</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ $addr->level }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $addr->parent?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $addr->pinyin ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
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
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    显示 {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} 条
                </div>
                <div class="flex items-center gap-1">
                    {{-- 上一页 --}}
                    @if($paginator->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed dark:bg-gray-700 dark:text-gray-500">
                            ← 上一页
                        </span>
                    @else
                        <button
                            wire:click="previousPage"
                            class="px-3 py-2 text-sm text-gray-700 bg-white border rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            ← 上一页
                        </button>
                    @endif

                    {{-- 页码 --}}
                    <span class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400">
                        第 {{ $paginator->currentPage() }} 页
                    </span>

                    {{-- 下一页 --}}
                    @if($paginator->hasMorePages())
                        <button
                            wire:click="nextPage"
                            class="px-3 py-2 text-sm text-gray-700 bg-white border rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            下一页 →
                        </button>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed dark:bg-gray-700 dark:text-gray-500">
                            下一页 →
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
