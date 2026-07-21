<?php

declare(strict_types=1);

namespace Tests\Feature\Xinfutong;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_xinfutong_employees_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('xinfutong_employees'));
    }

    public function test_xinfutong_employees_table_has_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'xinfutong_id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'employee_no'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'name'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'gender'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'phone'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'email'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'department'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'position'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'hire_date'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'status'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'raw_data'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'last_synced_at'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'created_at'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('xinfutong_employees', 'deleted_at'));
    }

    public function test_xinfutong_sync_logs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('xinfutong_sync_logs'));
    }

    public function test_xinfutong_sync_logs_table_has_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'employee_id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'sync_type'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'status'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'records_fetched'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'records_created'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'records_updated'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'error_message'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'started_at'));
        $this->assertTrue(Schema::hasColumn('xinfutong_sync_logs', 'completed_at'));
    }

    public function test_xinfutong_approval_requests_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('xinfutong_approval_requests'));
    }

    public function test_xinfutong_approval_requests_table_has_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'approval_no'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'approval_type'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'title'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'description'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'form_data'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'status'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'applicant_name'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'applicant_id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'xinfutong_approval_id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'error_message'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'raw_response'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'submitted_at'));
        $this->assertTrue(Schema::hasColumn('xinfutong_approval_requests', 'approved_at'));
    }

    public function test_xinfutong_accounts_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('xinfutong_accounts'));
    }

    public function test_xinfutong_accounts_table_has_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'company_name'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'base_url'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'app_id'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'authority_secret'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'sign_private_key'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'verify_public_key'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'timeout'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'is_default'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'is_active'));
        $this->assertTrue(Schema::hasColumn('xinfutong_accounts', 'remark'));
    }
}
