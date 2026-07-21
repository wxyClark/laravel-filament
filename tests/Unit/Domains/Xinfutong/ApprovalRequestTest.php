<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Xinfutong;

use App\Domains\Xinfutong\Enums\ApprovalStatus;
use App\Domains\Xinfutong\Models\ApprovalRequest;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApprovalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_approval_request(): void
    {
        $approval = ApprovalRequest::factory()->create();

        $this->assertDatabaseHas('xinfutong_approval_requests', [
            'id' => $approval->id,
            'approval_no' => $approval->approval_no,
        ]);
    }

    public function test_approval_no_must_be_unique(): void
    {
        ApprovalRequest::factory()->create(['approval_no' => 'APR001']);

        $this->expectException(QueryException::class);

        ApprovalRequest::factory()->create(['approval_no' => 'APR001']);
    }

    public function test_status_uses_approval_status_enum(): void
    {
        $approval = ApprovalRequest::factory()->create([
            'status' => ApprovalStatus::DRAFT,
        ]);

        $this->assertInstanceOf(ApprovalStatus::class, $approval->status);
        $this->assertEquals(ApprovalStatus::DRAFT, $approval->status);
    }

    public function test_form_data_is_cast_to_array(): void
    {
        $data = ['start_date' => '2026-07-21', 'reason' => '请假'];
        $approval = ApprovalRequest::factory()->create([
            'form_data' => $data,
        ]);

        $this->assertIsArray($approval->form_data);
        $this->assertEquals($data, $approval->form_data);
    }

    public function test_raw_response_is_cast_to_array(): void
    {
        $response = ['code' => 0, 'data' => ['id' => 123]];
        $approval = ApprovalRequest::factory()->create([
            'raw_response' => $response,
        ]);

        $this->assertIsArray($approval->raw_response);
        $this->assertEquals($response, $approval->raw_response);
    }

    public function test_submitted_at_is_cast_to_datetime(): void
    {
        $approval = ApprovalRequest::factory()->create([
            'submitted_at' => '2026-07-21 10:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $approval->submitted_at);
    }

    public function test_approval_status_enum_labels(): void
    {
        $this->assertEquals('草稿', ApprovalStatus::DRAFT->label());
        $this->assertEquals('审批中', ApprovalStatus::PENDING->label());
        $this->assertEquals('已通过', ApprovalStatus::APPROVED->label());
        $this->assertEquals('已驳回', ApprovalStatus::REJECTED->label());
        $this->assertEquals('已撤回', ApprovalStatus::WITHDRAWN->label());
    }

    public function test_approval_status_enum_colors(): void
    {
        $this->assertEquals('gray', ApprovalStatus::DRAFT->color());
        $this->assertEquals('warning', ApprovalStatus::PENDING->color());
        $this->assertEquals('success', ApprovalStatus::APPROVED->color());
        $this->assertEquals('danger', ApprovalStatus::REJECTED->color());
        $this->assertEquals('info', ApprovalStatus::WITHDRAWN->color());
    }
}
