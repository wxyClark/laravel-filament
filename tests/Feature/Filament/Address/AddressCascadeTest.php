<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\ViewAddressList;
use App\Models\Address;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Address Cascade Filter Tests
|--------------------------------------------------------------------------
|
| 测试地址列表页面的级联筛选行为：
| 省 → 市 → 区县 → 街道，逐级联动
|
*/

describe('Cascade Filter: Province Selection', function () {

    test('selecting province loads cities', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->assertViewHas('cities', function ($cities) use ($city) {
                return $cities->contains('id', $city->id);
            });
    });

    test('selecting province resets city, district, township', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedCityId', 999)
            ->set('selectedDistrictId', 999)
            ->set('selectedTownshipId', 999)
            ->set('selectedProvinceId', $province->id)
            ->assertSet('selectedCityId', null)
            ->assertSet('selectedDistrictId', null)
            ->assertSet('selectedTownshipId', null);
    });

    test('selecting province resets page to 1', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('page', 5)
            ->set('selectedProvinceId', $province->id)
            ->assertSet('page', 1);
    });

    test('clearing province selection resets cities', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->assertViewHas('cities', function ($cities) {
                return $cities->isNotEmpty();
            })
            ->set('selectedProvinceId', null)
            ->assertViewHas('cities', function ($cities) {
                return $cities->isEmpty();
            });
    });
});

describe('Cascade Filter: City Selection', function () {

    test('selecting city loads districts', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        $district = Address::create([
            'parent_id' => $city->id,
            'name' => '测试区',
            'code' => '990101',
            'level' => 'district',
            'level_num' => 4,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->set('selectedCityId', $city->id)
            ->assertViewHas('districts', function ($districts) use ($district) {
                return $districts->contains('id', $district->id);
            });
    });

    test('selecting city resets district and township', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->set('selectedDistrictId', 999)
            ->set('selectedTownshipId', 999)
            ->set('selectedCityId', $city->id)
            ->assertSet('selectedDistrictId', null)
            ->assertSet('selectedTownshipId', null);
    });
});

describe('Cascade Filter: District Selection', function () {

    test('selecting district loads townships', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        $district = Address::create([
            'parent_id' => $city->id,
            'name' => '测试区',
            'code' => '990101',
            'level' => 'district',
            'level_num' => 4,
        ]);

        $township = Address::create([
            'parent_id' => $district->id,
            'name' => '测试街道',
            'code' => '990101001',
            'level' => 'township',
            'level_num' => 5,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->set('selectedCityId', $city->id)
            ->set('selectedDistrictId', $district->id)
            ->assertViewHas('townships', function ($townships) use ($township) {
                return $townships->contains('id', $township->id);
            });
    });

    test('selecting district resets township', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        $district = Address::create([
            'parent_id' => $city->id,
            'name' => '测试区',
            'code' => '990101',
            'level' => 'district',
            'level_num' => 4,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->set('selectedCityId', $city->id)
            ->set('selectedTownshipId', 999)
            ->set('selectedDistrictId', $district->id)
            ->assertSet('selectedTownshipId', null);
    });
});

describe('Reset Filters', function () {

    test('resetFilters clears all selections', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->call('resetFilters')
            ->assertSet('selectedProvinceId', null)
            ->assertSet('selectedCityId', null)
            ->assertSet('selectedDistrictId', null)
            ->assertSet('selectedTownshipId', null)
            ->assertSet('page', 1);
    });

    test('resetFilters clears dropdown data', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->assertViewHas('cities', function ($cities) use ($city) {
                return $cities->contains('id', $city->id);
            })
            ->call('resetFilters')
            ->assertViewHas('cities', function ($cities) {
                return $cities->isEmpty();
            })
            ->assertViewHas('districts', function ($districts) {
                return $districts->isEmpty();
            })
            ->assertViewHas('townships', function ($townships) {
                return $townships->isEmpty();
            });
    });
});

describe('Filtered Results', function () {

    test('getFilteredAddresses returns all when no filter', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        Livewire::test(ViewAddressList::class)
            ->assertSet('totalResults', 2);
    });

    test('getFilteredAddresses filters by province', function () {
        $province1 = Address::create([
            'parent_id' => null,
            'name' => '测试省1',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $province2 = Address::create([
            'parent_id' => null,
            'name' => '测试省2',
            'code' => '980000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city = Address::create([
            'parent_id' => $province1->id,
            'name' => '测试市',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province1->id)
            ->assertSet('totalResults', 1);
    });

    test('getFilteredAddresses filters by city', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        $city1 = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市1',
            'code' => '990100',
            'level' => 'city',
            'level_num' => 3,
        ]);

        $city2 = Address::create([
            'parent_id' => $province->id,
            'name' => '测试市2',
            'code' => '990200',
            'level' => 'city',
            'level_num' => 3,
        ]);

        $district = Address::create([
            'parent_id' => $city1->id,
            'name' => '测试区',
            'code' => '990101',
            'level' => 'district',
            'level_num' => 4,
        ]);

        Livewire::test(ViewAddressList::class)
            ->set('selectedProvinceId', $province->id)
            ->set('selectedCityId', $city1->id)
            ->assertSet('totalResults', 1);
    });
});

describe('Pagination', function () {

    test('perPage default is 25', function () {
        Livewire::test(ViewAddressList::class)
            ->assertSet('perPage', 25);
    });

    test('changing perPage resets page to 1', function () {
        Livewire::test(ViewAddressList::class)
            ->set('page', 3)
            ->set('perPage', 50)
            ->assertSet('page', 1);
    });

    test('page default is 1', function () {
        Livewire::test(ViewAddressList::class)
            ->assertSet('page', 1);
    });
});
