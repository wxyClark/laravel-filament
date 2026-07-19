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
</x-filament-panels::page>
