<x-filament-panels::page>
    @if($address)
        {{-- 面包屑导航 --}}
        <div class="mb-6">
            <nav class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('filament.admin.pages.view-address-list') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    地址管理
                </a>
                <x-heroicon-m-chevron-right class="h-4 w-4" />
                @if(!empty($parentChain))
                    @foreach($parentChain as $parent)
                        <a href="{{ route('filament.admin.pages.address', ['id' => $parent->id]) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            {{ $parent->name }}
                        </a>
                        <x-heroicon-m-chevron-right class="h-4 w-4" />
                    @endforeach
                @endif
                <span class="text-gray-900 dark:text-white font-medium">{{ $address->name }}</span>
            </nav>
        </div>

        <div class="space-y-6">
            {{-- 1. 上级地址 --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-map class="h-5 w-5 text-blue-500" />
                        上级地址
                    </h3>
                </div>
                <div class="p-6">
                    @if(!empty($parentChain))
                        <div class="flex items-center flex-wrap gap-1.5">
                            <a href="{{ route('filament.admin.pages.view-address-list') }}" class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">
                                中国
                            </a>
                            @foreach($parentChain as $index => $parent)
                                <x-heroicon-m-chevron-right class="h-3.5 w-3.5 text-gray-400" />
                                <a href="{{ route('filament.admin.pages.address', ['id' => $parent->id]) }}"
                                   class="text-sm {{ $index === count($parentChain) - 1 ? 'font-medium text-gray-900 dark:text-white' : 'text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300' }} transition-colors">
                                    {{ $parent->name }}
                                </a>
                            @endforeach
                            <x-heroicon-m-chevron-right class="h-3.5 w-3.5 text-gray-400" />
                            <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $address->name }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-globe-alt class="h-4 w-4" />
                            中国 / <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $address->name }}</span>（顶级地址）
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. 基本信息 --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-primary-500" />
                        基本信息
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">ID</p>
                            <p class="text-sm font-mono text-gray-900 dark:text-white">{{ $address->id }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">名称</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $address->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">编码</p>
                            <p class="text-sm font-mono text-gray-900 dark:text-white">{{ $address->code }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">层级</p>
                            @if($address->level === 'province')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">省级</span>
                            @elseif($address->level === 'city')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">地级</span>
                            @elseif($address->level === 'district')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">县级</span>
                            @elseif($address->level === 'township')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">街道</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ $address->level }}</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">拼音</p>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $address->pinyin ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">排序</p>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $address->sort ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">创建时间</p>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $address->created_at?->format('Y-m-d H:i') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">更新时间</p>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $address->updated_at?->format('Y-m-d H:i') ?? '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">合并路径</p>
                            <p class="text-sm text-gray-900 dark:text-white font-mono text-xs break-all">{{ $address->full_path }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. 下级统计（三项一行） --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="h-5 w-5 text-green-500" />
                        下级统计
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-stretch gap-4">
                        <div class="flex-1 text-center py-3 px-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $childCount }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">直接下级</p>
                        </div>
                        <div class="flex-1 text-center py-3 px-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $totalChildCount }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">全部下级</p>
                        </div>
                        <div class="flex-1 text-center py-3 px-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $siblingCount }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">同级地址</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. 下级地址 --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-folder-open class="h-5 w-5 text-green-500" />
                        下级地址
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">共 {{ $childCount }} 个直接下级</span>
                </div>
                <div class="p-6">
                    @if($children->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach($children as $child)
                                <a href="{{ route('filament.admin.pages.address', ['id' => $child->id]) }}"
                                   class="group flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200">
                                    <div class="flex-shrink-0">
                                        @if($child->level === 'province')
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 text-xs font-bold">省</span>
                                        @elseif($child->level === 'city')
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 text-xs font-bold">市</span>
                                        @elseif($child->level === 'district')
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400 text-xs font-bold">县</span>
                                        @elseif($child->level === 'township')
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 text-xs font-bold">街</span>
                                        @else
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-bold">?</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 truncate">{{ $child->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $child->code }}</p>
                                    </div>
                                    <x-heroicon-m-chevron-right class="h-4 w-4 text-gray-400 group-hover:text-primary-500 transition-colors" />
                                </a>
                            @endforeach
                        </div>
                        @if($totalChildCount > $childCount)
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                另有 {{ $totalChildCount - $childCount }} 个更深层级的下级地址
                            </p>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-folder class="h-12 w-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">暂无下级地址</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 5. 快捷操作 --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-gray-500" />
                        快捷操作
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('filament.admin.pages.view-address-list') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400">
                            <x-heroicon-o-arrow-left class="h-4 w-4" />
                            返回列表
                        </a>
                        @if($address->parent_id)
                            <a href="{{ route('filament.admin.pages.address', ['id' => $address->parent_id]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400">
                                <x-heroicon-o-arrow-up class="h-4 w-4" />
                                上级：{{ $address->parent?->name }}
                            </a>
                        @endif
                        @if($childCount > 0)
                            <a href="{{ route('filament.admin.pages.address', ['id' => $children->first()->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400">
                                <x-heroicon-o-arrow-down class="h-4 w-4" />
                                下级：{{ $children->first()->name }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 6. 同级地址 --}}
            @if($siblingCount > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-squares-2x2 class="h-5 w-5 text-blue-500" />
                            同级地址
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">共 {{ $siblingCount }} 个同级</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach($siblings as $sibling)
                                <a href="{{ route('filament.admin.pages.address', ['id' => $sibling->id]) }}"
                                   class="group flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 {{ $sibling->id === $address->id ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 truncate {{ $sibling->id === $address->id ? 'text-primary-600 dark:text-primary-400' : '' }}">
                                            {{ $sibling->name }}
                                            @if($sibling->id === $address->id)
                                                <span class="text-xs text-primary-500">(当前)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $sibling->code }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-16">
            <x-heroicon-o-exclamation-circle class="h-16 w-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
            <p class="text-lg text-gray-500 dark:text-gray-400">地址不存在</p>
            <a href="{{ route('filament.admin.pages.view-address-list') }}" class="mt-4 inline-flex items-center gap-2 text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                <x-heroicon-m-arrow-left class="h-4 w-4" />
                返回列表
            </a>
        </div>
    @endif
</x-filament-panels::page>
