<?php

use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Address::truncate();
    Cache::flush();
});

test('api can get all addresses', function () {
    Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $response = $this->getJson('/api/addresses');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', '北京市');
});

test('api can get children by parent id', function () {
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

test('api can get by level', function () {
    Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $response = $this->getJson('/api/addresses/by-level/province');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('api can find by code', function () {
    $address = Address::create([
        'parent_id' => null,
        'name' => '北京市',
        'code' => '110000',
        'level' => 'province',
        'level_num' => 2,
    ]);

    $response = $this->getJson('/api/addresses/find/110000');

    $response->assertStatus(200)
        ->assertJsonPath('data.name', '北京市')
        ->assertJsonPath('data.code', '110000');
});

test('api returns 404 for non-existent code', function () {
    $response = $this->getJson('/api/addresses/find/999999');

    $response->assertStatus(404)
        ->assertJsonPath('message', '地址不存在');
});

test('api can get address tree', function () {
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

    $response = $this->getJson('/api/addresses/tree');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', '北京市')
        ->assertJsonPath('data.0.children.0.name', '朝阳区');
});
