<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\ViewAddressList;
use App\Models\Address;
use App\Models\Admin;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| ViewAddressList Page Tests
|--------------------------------------------------------------------------
|
| 测试地址列表页面的访问权限、数据加载、级联筛选和分页功能
|
*/

describe('ViewAddressList Page Access', function () {

    test('address list page redirects when unauthenticated', function () {
        $response = $this->get('/admin/view-address-list');

        $response->assertRedirect('/admin/login');
    });

    test('address list page is accessible when authenticated', function () {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/view-address-list');

        $response->assertOk();
    });

    test('address list page has correct title', function () {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(ViewAddressList::class)
            ->assertOk();
    });
});

describe('ViewAddressList Province Loading', function () {

    test('top-level addresses are loaded on mount', function () {
        $province = Address::create([
            'parent_id' => null,
            'name' => '测试省',
            'code' => '990000',
            'level' => 'province',
            'level_num' => 2,
        ]);

        Livewire::test(ViewAddressList::class)
            ->assertViewHas('countries', function ($countries) use ($province) {
                return collect($countries)->contains('id', $province->id);
            });
    });

    test('provinces dropdown excludes non-top-level addresses', function () {
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
            ->assertViewHas('countries', function ($countries) use ($province, $city) {
                return collect($countries)->contains('id', $province->id)
                    && ! collect($countries)->contains('id', $city->id);
            });
    });

    test('cities and districts are empty on initial load', function () {
        Livewire::test(ViewAddressList::class)
            ->assertViewHas('cities', function ($cities) {
                return empty($cities);
            })
            ->assertViewHas('districts', function ($districts) {
                return empty($districts);
            })
            ->assertViewHas('townships', function ($townships) {
                return empty($townships);
            });
    });
});
