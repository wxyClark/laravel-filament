<?php

namespace App\Services;

use App\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AddressService
{
    protected int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('app.address_cache_ttl', 3600);
    }

    /**
     * 构建地址查询（页面显示和导出共用）
     *
     * @param  array{parent_id?: int, level?: string, keyword?: string}  $filters
     */
    public function buildQuery(array $filters = []): Builder
    {
        $query = Address::query()->with('parent');

        if (! empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (! empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('pinyin', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    public function getAllAddresses(): Collection
    {
        return Cache::remember('addresses.all', $this->cacheTtl, function () {
            return Address::with('children')->orderBy('level_num')->orderBy('sort')->get();
        });
    }

    public function getChildrenByParentId(?int $parentId): Collection
    {
        return Cache::remember("addresses.parent.{$parentId}", $this->cacheTtl, function () use ($parentId) {
            return Address::where('parent_id', $parentId)
                ->orderBy('id')
                ->get();
        });
    }

    public function getByLevel(string $level): Collection
    {
        return Cache::remember("addresses.level.{$level}", $this->cacheTtl, function () use ($level) {
            return Address::where('level', $level)
                ->orderBy('sort')
                ->orderBy('name')
                ->get();
        });
    }

    public function findByCode(string $code): ?Address
    {
        return Address::where('code', $code)->first();
    }

    public function getAddressTree(?int $parentId = null): Collection
    {
        $children = $this->getChildrenByParentId($parentId);

        return $children->map(function (Address $address) {
            return [
                'id' => $address->id,
                'name' => $address->name,
                'code' => $address->code,
                'level' => $address->level,
                'level_num' => $address->level_num,
                'children' => $this->getAddressTree($address->id),
            ];
        });
    }

    public function fetchFromNationalBureau(): array
    {
        $response = Http::timeout(30)->get('https://www.stats.gov.cn/sj/tjbz/tjyqhdmhcxhfdm/2023/index.html');

        if ($response->successful()) {
            return $this->parseHtmlData($response->body());
        }

        throw new \RuntimeException('无法从国家统计局获取数据');
    }

    protected function parseHtmlData(string $html): array
    {
        return [];
    }

    public function importAddresses(array $data): int
    {
        Address::query()->withTrashed()->get()->each->forceDelete();
        Cache::forget('addresses.all');

        // 按 level_num 排序导入
        $sorted = collect($data)->sortBy('level_num')->values()->toArray();
        $count = 0;

        foreach ($sorted as $item) {
            $parentId = null;
            if (! empty($item['parent_code'])) {
                $parent = Address::where('code', $item['parent_code'])->first();
                if ($parent) {
                    $parentId = $parent->id;
                }
            }

            Address::create([
                'parent_id' => $parentId,
                'name' => $item['name'],
                'code' => $item['code'],
                'level' => $item['level'],
                'level_num' => $item['level_num'],
                'pinyin' => $item['pinyin'] ?? null,
                'merge_path' => $item['merge_path'] ?? null,
                'sort' => $item['sort'] ?? 0,
            ]);
            $count++;
        }

        return $count;
    }

    public function clearCache(): void
    {
        Cache::forget('addresses.all');
    }
}
