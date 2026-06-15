<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        // 示例数据：中国行政区划
        $data = [
            // 国家
            [
                'id' => 1,
                'parent_id' => null,
                'name' => '中华人民共和国',
                'code' => 'CHN',
                'level' => 'country',
                'level_num' => 1,
                'pinyin' => 'zhonghuarenmingongheguo',
                'merge_path' => json_encode(['中华人民共和国']),
                'sort' => 0,
            ],
            // 省级
            [
                'id' => 2,
                'parent_id' => 1,
                'name' => '北京市',
                'code' => '110000',
                'level' => 'province',
                'level_num' => 2,
                'pinyin' => 'beijing',
                'merge_path' => json_encode(['中华人民共和国', '北京市']),
                'sort' => 0,
            ],
            [
                'id' => 3,
                'parent_id' => 1,
                'name' => '上海市',
                'code' => '310000',
                'level' => 'province',
                'level_num' => 2,
                'pinyin' => 'shanghai',
                'merge_path' => json_encode(['中华人民共和国', '上海市']),
                'sort' => 0,
            ],
            // 市级
            [
                'id' => 4,
                'parent_id' => 2,
                'name' => '北京市',
                'code' => '110100',
                'level' => 'city',
                'level_num' => 3,
                'pinyin' => 'beijing',
                'merge_path' => json_encode(['中华人民共和国', '北京市', '北京市']),
                'sort' => 0,
            ],
            // 区级
            [
                'id' => 5,
                'parent_id' => 4,
                'name' => '东城区',
                'code' => '110101',
                'level' => 'district',
                'level_num' => 4,
                'pinyin' => 'dongchengqu',
                'merge_path' => json_encode(['中华人民共和国', '北京市', '北京市', '东城区']),
                'sort' => 0,
            ],
            [
                'id' => 6,
                'parent_id' => 4,
                'name' => '西城区',
                'code' => '110102',
                'level' => 'district',
                'level_num' => 4,
                'pinyin' => 'xichengqu',
                'merge_path' => json_encode(['中华人民共和国', '北京市', '北京市', '西城区']),
                'sort' => 0,
            ],
        ];

        foreach ($data as $item) {
            DB::table('addresses')->updateOrInsert(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
