<?php

declare(strict_types=1);

namespace Tests\Feature\Xinfutong;

use App\Domains\Xinfutong\Models\Account;
use App\Filament\Admin\Resources\XinfutongAccountResource\Pages\ListXinfutongAccounts;
use App\Models\Admin;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_account_list_page_is_accessible(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/xinfutong-accounts')
            ->assertSuccessful();
    }

    public function test_account_resource_can_render(): void
    {
        $this->actingAs($this->admin, 'admin');

        Account::factory()->count(3)->create();

        Livewire::test(ListXinfutongAccounts::class)
            ->assertSuccessful();
    }

    public function test_account_model_can_be_created(): void
    {
        $account = Account::factory()->create([
            'company_name' => '测试企业有限公司',
            'app_id' => 'test-app-id',
        ]);

        $this->assertDatabaseHas('xinfutong_accounts', [
            'company_name' => '测试企业有限公司',
            'app_id' => 'test-app-id',
        ]);
    }

    public function test_account_model_can_be_updated(): void
    {
        $account = Account::factory()->create([
            'company_name' => '原企业名称',
        ]);

        $account->update(['company_name' => '新企业名称']);

        $this->assertDatabaseHas('xinfutong_accounts', [
            'id' => $account->id,
            'company_name' => '新企业名称',
        ]);
    }

    public function test_company_name_must_be_unique(): void
    {
        Account::factory()->create(['company_name' => '已存在企业']);

        $this->expectException(QueryException::class);

        Account::factory()->create(['company_name' => '已存在企业']);
    }
}
