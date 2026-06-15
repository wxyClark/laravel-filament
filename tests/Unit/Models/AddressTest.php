<?php

use App\Models\Address;
use App\Services\AddressService;

test('address model exists', function () {
    expect(class_exists(Address::class))->toBeTrue();
});

test('address can be created', function () {
    $address = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
        'pinyin' => 'beijing',
        'merge_path' => ['中华人民共和国', '北京市'],
        'sort' => 0,
    ]);

    expect($address)->toBeInstanceOf(Address::class)
        ->and($address->name)->toBe('北京市')
        ->and($address->code)->toBe('110000');
});

test('address has parent relationship', function () {
    $parent = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $child = Address::create([
        'parent_id' => $parent->id,
        'name' => '朝阳区',
        'code' => '110105',
        'level' => 'district',
        'level_num' => 4,
    ]);

    expect($child->parent->id)->toBe($parent->id);
});

test('address can have multiple children', function () {
    $parent = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $child1 = Address::create([
        'parent_id' => $parent->id,
        'name' => '朝阳区',
        'code' => '110105',
        'level' => 'district',
        'level_num' => 4,
    ]);

    $child2 = Address::create([
        'parent_id' => $parent->id,
        'name' => '海淀区',
        'code' => '110108',
        'level' => 'district',
        'level_num' => 4,
    ]);

    expect($parent->children)->toHaveCount(2);
});

test('address tree can be built', function () {
    $parent = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $child = Address::create([
        'parent_id' => $parent->id,
        'name' => '朝阳区',
        'code' => '110105',
        'level' => 'district',
        'level_num' => 4,
    ]);

    $tree = app(AddressService::class)->getAddressTree();

    expect($tree)->toHaveCount(1)
        ->and($tree->first()['children'])->toHaveCount(1);
});

test('address full path attribute works', function () {
    $address = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
        'merge_path' => ['中华人民共和国', '北京市'],
    ]);

    expect($address->full_path)->toBe('中华人民共和国/北京市');
});

test('address can be soft deleted', function () {
    $address = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $address->delete();

    expect(Address::where('id', $address->id)->count())->toBe(0);
    expect(Address::withTrashed()->where('id', $address->id)->count())->toBe(1);
});

test('address can be found by code', function () {
    $address = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $found = Address::where('code', '110000')->first();

    expect($found->id)->toBe($address->id);
});

test('address levels are correctly assigned', function () {
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

    expect($province->level_num)->toBe(2)
        ->and($city->level_num)->toBe(3)
        ->and($district->level_num)->toBe(4);
});
