<x-filament-panels::page>
    <div class="space-y-4">
        {{-- 级联筛选区 --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-900">
            <div class="flex gap-4">
                {{-- 省份 --}}
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">省份</label>
                    <select
                        wire:model.live="selectedProvinceId"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">全部</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 城市 --}}
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">城市</label>
                    <select
                        wire:model.live="selectedCityId"
                        @disabled($cities->isEmpty())
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                    >
                        <option value="">全部</option>
                        @foreach($cities as $city)
                            <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 区县 --}}
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">区县</label>
                    <select
                        wire:model.live="selectedDistrictId"
                        @disabled($districts->isEmpty())
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                    >
                        <option value="">全部</option>
                        @foreach($districts as $district)
                            <option value="{{ $district['id'] }}">{{ $district['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 街道 --}}
                <div class="flex flex-col gap-1 flex-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">街道</label>
                    <select
                        wire:model.live="selectedTownshipId"
                        @disabled($townships->isEmpty())
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                    >
                        <option value="">全部</option>
                        @foreach($townships as $township)
                            <option value="{{ $township['id'] }}">{{ $township['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 重置按钮 --}}
                <div class="flex flex-col gap-1" style="width: 100px">
                    <label class="text-sm font-medium text-transparent">操作</label>
                    <button
                        wire:click="resetFilters"
                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                        重置
                    </button>
                </div>
            </div>
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
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell sort="false">
                        <div class="text-sm font-semibold">ID</div>
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell sort="false">
                        <div class="text-sm font-semibold">名称</div>
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell sort="false">
                        <div class="text-sm font-semibold">代码</div>
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell sort="false">
                        <div class="text-sm font-semibold">层级</div>
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell sort="false">
                        <div class="text-sm font-semibold">上级</div>
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell sort="false">
                        <div class="text-sm font-semibold">拼音</div>
                    </x-filament-tables::header-cell>
                </x-slot>

                @forelse($this->getFilteredAddresses() as $addr)
                    <x-filament-tables::row>
                        <x-filament-tables::cell>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $addr->id }}</div>
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <div class="text-sm font-medium text-gray-950 dark:text-white">{{ $addr->name }}</div>
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <div class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ $addr->code }}</div>
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @if($addr->level === 'province')
                                <x-filament-tables::badge value="省级" color="info" />
                            @elseif($addr->level === 'city')
                                <x-filament-tables::badge value="地级" color="success" />
                            @elseif($addr->level === 'district')
                                <x-filament-tables::badge value="县级" color="warning" />
                            @elseif($addr->level === 'township')
                                <x-filament-tables::badge value="街道" color="danger" />
                            @else
                                <x-filament-tables::badge :value="$addr->level" color="gray" />
                            @endif
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $addr->parent?->name ?? '-' }}</div>
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $addr->pinyin ?? '-' }}</div>
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                @empty
                    <x-filament-tables::empty-state icon="heroicon-o-magnifying-glass" />
                @endforelse
            </x-filament-tables::table>
        </div>

        {{-- 分页导航 --}}
        @php
            $paginator = $this->getFilteredAddresses();
        @endphp
        @if($paginator->hasPages())
            <div class="filament-tables-pagination flex items-center justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    显示 {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} 条
                </div>
                <div class="flex items-center gap-1">
                    @if($paginator->onFirstPage())
                        <span class="filament-tables-pagination-item filament-tables-pagination-item-disabled">
                            ← 上一页
                        </span>
                    @else
                        <button
                            wire:click="previousPage"
                            class="filament-tables-pagination-item"
                        >
                            ← 上一页
                        </button>
                    @endif

                    <span class="filament-tables-pagination-item">
                        第 {{ $paginator->currentPage() }} 页
                    </span>

                    @if($paginator->hasMorePages())
                        <button
                            wire:click="nextPage"
                            class="filament-tables-pagination-item"
                        >
                            下一页 →
                        </button>
                    @else
                        <span class="filament-tables-pagination-item filament-tables-pagination-item-disabled">
                            下一页 →
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
