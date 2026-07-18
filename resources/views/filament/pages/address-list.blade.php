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
                                <button wire:click="viewDetail({{ $addr->id }})" class="text-primary-600 hover:text-primary-900 dark:text-primary-400">
                                    详情
                                </button>
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

    {{-- 详情弹窗 --}}
    @if($showDetailModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDetailModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full dark:bg-gray-800">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    地址详情 - {{ $detailAddress?->name ?? '' }}
                                </h3>
                                <div class="mt-4 space-y-4">
                                    {{-- 基本信息 --}}
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">基本信息</h4>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <span class="text-xs text-gray-500">ID</span>
                                                <p class="text-sm font-medium">{{ $detailAddress?->id }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500">名称</span>
                                                <p class="text-sm font-medium">{{ $detailAddress?->name }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500">编码</span>
                                                <p class="text-sm font-mono">{{ $detailAddress?->code }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500">层级</span>
                                                <p class="text-sm font-medium">{{ $detailAddress?->level }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500">拼音</span>
                                                <p class="text-sm">{{ $detailAddress?->pinyin ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500">排序</span>
                                                <p class="text-sm">{{ $detailAddress?->sort ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 上级地址链 --}}
                                    <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-3">📍 上级地址</h4>
                                        @if($parentChain->count() > 0)
                                            <div class="flex items-center flex-wrap gap-1 text-sm">
                                                <span class="text-blue-600 dark:text-blue-400">中国</span>
                                                @foreach($parentChain as $parent)
                                                    <span class="text-gray-400">/</span>
                                                    <span class="text-blue-600 dark:text-blue-400">{{ $parent->name }}</span>
                                                @endforeach
                                                <span class="text-gray-400">/</span>
                                                <span class="text-blue-800 dark:text-blue-200 font-semibold">{{ $detailAddress?->name }}</span>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500">顶级地址</p>
                                        @endif
                                    </div>

                                    {{-- 下级地址统计 --}}
                                    <div class="bg-green-50 dark:bg-green-900 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-green-700 dark:text-green-300 mb-3">📊 下级地址统计</h4>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="bg-white dark:bg-gray-600 rounded p-3 text-center">
                                                <p class="text-2xl font-bold text-green-600">{{ $childCount }}</p>
                                                <p class="text-xs text-gray-500">直接下级</p>
                                            </div>
                                            <div class="bg-white dark:bg-gray-600 rounded p-3 text-center">
                                                <p class="text-2xl font-bold text-blue-600">{{ $totalChildCount }}</p>
                                                <p class="text-xs text-gray-500">全部下级</p>
                                            </div>
                                        </div>
                                        @if($children->count() > 0)
                                            <div class="mt-3">
                                                <p class="text-xs text-gray-500 mb-2">直接下级列表 (前10个)：</p>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($children->take(10) as $child)
                                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">{{ $child->name }}</span>
                                                    @endforeach
                                                    @if($children->count() > 10)
                                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">+{{ $children->count() - 10 }} 更多</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeDetailModal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                            关闭
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
