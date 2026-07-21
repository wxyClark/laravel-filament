<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Xinfutong;

use App\Domains\Xinfutong\Models\Account;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_account(): void
    {
        $account = Account::factory()->create();

        $this->assertDatabaseHas('xinfutong_accounts', [
            'id' => $account->id,
            'company_name' => $account->company_name,
        ]);
    }

    public function test_company_name_must_be_unique(): void
    {
        Account::factory()->create(['company_name' => '测试企业']);

        $this->expectException(QueryException::class);

        Account::factory()->create(['company_name' => '测试企业']);
    }

    public function test_get_default_returns_default_account(): void
    {
        Account::factory()->create(['is_default' => false]);
        $default = Account::factory()->create(['is_default' => true]);

        $result = Account::getDefault();

        $this->assertNotNull($result);
        $this->assertEquals($default->id, $result->id);
    }

    public function test_get_default_returns_null_when_no_default(): void
    {
        Account::factory()->create(['is_default' => false]);

        $result = Account::getDefault();

        $this->assertNull($result);
    }

    public function test_get_active_returns_only_active_accounts(): void
    {
        Account::factory()->create(['is_active' => true]);
        Account::factory()->create(['is_active' => true]);
        Account::factory()->create(['is_active' => false]);

        $result = Account::getActive();

        $this->assertCount(2, $result);
    }

    public function test_set_as_default_unsets_other_defaults(): void
    {
        $account1 = Account::factory()->create(['is_default' => true]);
        $account2 = Account::factory()->create(['is_default' => false]);

        $account2->setAsDefault();

        $account1->refresh();
        $account2->refresh();

        $this->assertFalse($account1->is_default);
        $this->assertTrue($account2->is_default);
    }

    public function test_hidden_fields_are_not_serialized(): void
    {
        $account = Account::factory()->create([
            'sign_private_key' => 'secret-key',
            'authority_secret' => 'secret-value',
        ]);

        $array = $account->toArray();

        $this->assertArrayNotHasKey('sign_private_key', $array);
        $this->assertArrayNotHasKey('authority_secret', $array);
    }

    public function test_account_has_sync_logs_relation(): void
    {
        $account = Account::factory()->create();

        $this->assertEmpty($account->syncLogs);
    }

    public function test_account_has_approval_requests_relation(): void
    {
        $account = Account::factory()->create();

        $this->assertEmpty($account->approvalRequests);
    }
}
