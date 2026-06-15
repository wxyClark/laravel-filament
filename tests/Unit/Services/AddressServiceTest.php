<?php

use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Support\Facades\Cache;

test('address service can get all addresses', function () {
    Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $service = app(AddressService::class);
    $addresses = $service->getAllAddresses();

    expect($addresses)->toHaveCount(1)
        ->and($addresses->first()->name)->toBe('北京市');
});

test('address service can get children by parent id', function () {
    $province = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    Address::create([
        'parent_id' => $province->id,
        'name' => '朝阳区',
        'code' => '110105',
        'level' => 'district',
        'level_num' => 4,
    ]);

    $service = app(AddressService::class);
    $children = $service->getChildrenByParentId($province->id);

    expect($children)->toHaveCount(1)
        ->and($children->first()->name)->toBe('朝阳区');
});

test('address service can get by level', function () {
    Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    Address::create([
        'parent_id' => null,
        'name' => '上海市',
        'code' => '310000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $service = app(AddressService::class);
    $provinces = $service->getByLevel('province');

    expect($provinces)->toHaveCount(2);
});

test('address service can find by code', function () {
    $address = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $service = app(AddressService::class);
    $found = $service->findByCode('110000');

    expect($found)->toBeInstanceOf(Address::class)
        ->and($found->name)->toBe('北京市');
});

test('address service tree returns correct structure', function () {
    $province = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $city = Address::create([
        'parent_id' => $province->id,
        'name' => '北京市',
        'code' => '110100',
        'level' => 'city',
        'level_num' => 3,
    ]);

    $district = Address::create([
        'parent_id' => $city->id,
        'name' => '朝阳区',
        'code' => '110105',
        'level' => 'district',
        'level_num' => 4,
    ]);

    $service = app(AddressService::class);
    $tree = $service->getAddressTree();

    expect($tree)->toHaveCount(1)
        ->and($tree->first()['name'])->toBe('北京市')
        ->and($tree->first()['children'])->toHaveCount(1);
});

test('address service cache works', function () {
    Cache::forget('addresses.all');

    Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $service = app(AddressService::class);
    $first = $service->getAllAddresses();
    $second = $service->getAllAddresses();

    expect($first)->toHaveCount(1);
    // 第二次应该从缓存获取
    expect(Cache::has('addresses.all'))->toBeTrue();
});
