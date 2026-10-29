<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\AddressImport\HmtRegions;
use Overtrue\Pinyin\Pinyin;

class AddressSeeder extends Seeder
{
    private array $codeToId = [];

    private Pinyin $pinyin;

    public function run(): void
    {
        $this->pinyin = app(Pinyin::class);

        DB::table('addresses')->truncate();

        $json = file_get_contents(database_path('seeders/pcas-code.json'));
        $data = json_decode($json, true);

        $this->addHongKongMacauTaiwan($data);

        $flat = $this->flatten($data);

        DB::transaction(function () use ($flat) {
            $this->insertLevel($flat, 2);
            $this->insertLevel($flat, 3);
            $this->insertLevel($flat, 4);
            $this->insertLevel($flat, 5);
        });
    }

    private function addHongKongMacauTaiwan(array &$data): void
    {
        foreach (HmtRegions::items() as $item) {
            $data[] = $item;
        }
    }

    private function flatten(array $items, ?string $parentCode = null, array $path = []): array
    {
        $result = [];

        foreach ($items as $item) {
            $code = $item['code'];
            $name = $item['name'];
            $codeLen = strlen($code);

            $level = match (true) {
                $codeLen <= 2 => 2,
                $codeLen <= 4 => 3,
                $codeLen <= 6 => 4,
                default => 5,
            };

            $currentPath = array_merge($path, [$name]);

            $result[] = [
                'code' => $code,
                'name' => $name,
                'level' => match ($level) {
                    2 => 'province',
                    3 => 'city',
                    4 => 'district',
                    default => 'township',
                },
                'level_num' => $level,
                'parent_code' => $parentCode,
                'merge_path' => $currentPath,
            ];

            if (isset($item['children'])) {
                $result = array_merge($result, $this->flatten($item['children'], $code, $currentPath));
            }
        }

        return $result;
    }

    private function insertLevel(array $flat, int $targetLevelNum): void
    {
        $items = array_filter($flat, fn ($item) => $item['level_num'] === $targetLevelNum);

        $batch = [];

        foreach ($items as $item) {
            $parentId = null;
            if ($item['parent_code'] !== null && isset($this->codeToId[$item['parent_code']])) {
                $parentId = $this->codeToId[$item['parent_code']];
            }

            $pinyin = $targetLevelNum <= 4
                ? $this->pinyin->permalink($item['name'], '')
                : '';

            $batch[] = [
                'parent_id' => $parentId,
                'name' => $item['name'],
                'code' => $item['code'],
                'level' => $item['level'],
                'level_num' => $item['level_num'],
                'pinyin' => $pinyin,
                'merge_path' => json_encode($item['merge_path'], JSON_UNESCAPED_UNICODE),
                'sort' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 2000) {
                $this->insertBatch($batch);
            }
        }

        if ($batch !== []) {
            $this->insertBatch($batch);
        }
    }

    private function insertBatch(array &$batch): void
    {
        DB::table('addresses')->insert($batch);

        $codes = array_column($batch, 'code');
        $rows = DB::table('addresses')
            ->whereIn('code', $codes)
            ->pluck('id', 'code')
            ->toArray();

        foreach ($codes as $code) {
            if (isset($rows[$code])) {
                $this->codeToId[$code] = $rows[$code];
            }
        }

        $batch = [];
    }
}
