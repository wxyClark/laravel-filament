<x-filament-panels::page>
    <div class="space-y-4">
        <h2 class="text-xl font-bold">地址树形浏览</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($addressTree as $province)
                <div class="border rounded-lg p-4">
                    <h3 class="font-bold text-lg">{{ $province['name'] }}</h3>
                    <p class="text-gray-500 text-sm">代码: {{ $province['code'] }}</p>
                    @if($province['children']->isNotEmpty())
                        <div class="mt-2 space-y-1">
                            @foreach($province['children'] as $city)
                                <div class="ml-4">
                                    <p class="font-medium">{{ $city['name'] }}</p>
                                    @if($city['children']->isNotEmpty())
                                        <div class="ml-4 mt-1 space-y-1">
                                            @foreach($city['children'] as $district)
                                                <p class="text-gray-600">- {{ $district['name'] }}</p>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4">地址列表</h2>
        <x-filament-tables::wrapper>
            <table class="w-full">
                <thead>
                    <tr>
                        <th>名称</th>
                        <th>代码</th>
                        <th>层级</th>
                        <th>上级</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Address::orderBy('level_num')->orderBy('sort')->get() as $addr)
                        <tr class="border-t">
                            <td class="p-2">{{ $addr->name }}</td>
                            <td class="p-2">{{ $addr->code }}</td>
                            <td class="p-2">
                                <span class="px-2 py-1 rounded text-xs
                                    {{ $addr->level === 'province' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $addr->level === 'city' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $addr->level === 'district' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                ">{{ $addr->level }}</span>
                            </td>
                            <td class="p-2">{{ $addr->parent?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament-tables::wrapper>
    </div>
</x-filament-panels::page>
