<?php

declare(strict_types=1);

namespace Tests\Feature\Xinfutong;

use App\Domains\Xinfutong\Enums\ApprovalStatus;
use App\Domains\Xinfutong\Models\ApprovalRequest;
use App\Filament\Admin\Resources\XinfutongApprovalResource\Pages\ListXinfutongApprovals;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApprovalResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_approval_list_page_is_accessible(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/xinfutong-approvals')
            ->assertSuccessful();
    }

    public function test_approval_resource_can_render(): void
    {
        $this->actingAs($this->admin, 'admin');

        ApprovalRequest::factory()->count(3)->create();

        Livewire::test(ListXinfutongApprovals::class)
            ->assertSuccessful();
    }

    public function test_approval_model_can_be_created(): void
    {
        $approval = ApprovalRequest::factory()->create([
            'approval_type' => 'leave',
            'title' => '测试请假申请',
            'applicant_name' => '张三',
        ]);

        $this->assertDatabaseHas('xinfutong_approval_requests', [
            'approval_type' => 'leave',
            'title' => '测试请假申请',
            'applicant_name' => '张三',
        ]);
    }

    public function test_approval_status_enum_values(): void
    {
        $this->assertEquals('draft', ApprovalStatus::DRAFT->value);
        $this->assertEquals('pending', ApprovalStatus::PENDING->value);
        $this->assertEquals('approved', ApprovalStatus::APPROVED->value);
        $this->assertEquals('rejected', ApprovalStatus::REJECTED->value);
        $this->assertEquals('withdrawn', ApprovalStatus::WITHDRAWN->value);
    }

    public function test_approval_status_enum_labels(): void
    {
        $this->assertEquals('草稿', ApprovalStatus::DRAFT->label());
        $this->assertEquals('审批中', ApprovalStatus::PENDING->label());
        $this->assertEquals('已通过', ApprovalStatus::APPROVED->label());
        $this->assertEquals('已驳回', ApprovalStatus::REJECTED->label());
        $this->assertEquals('已撤回', ApprovalStatus::WITHDRAWN->label());
    }
}
