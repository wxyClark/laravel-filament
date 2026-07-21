<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Xinfutong;

use App\Domains\Xinfutong\Enums\SyncStatus;
use App\Domains\Xinfutong\Models\SyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SyncLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_sync_log(): void
    {
        $log = SyncLog::factory()->create();

        $this->assertDatabaseHas('xinfutong_sync_logs', [
            'id' => $log->id,
            'sync_type' => $log->sync_type,
        ]);
    }

    public function test_status_uses_sync_status_enum(): void
    {
        $log = SyncLog::factory()->create([
            'status' => SyncStatus::PENDING,
        ]);

        $this->assertInstanceOf(SyncStatus::class, $log->status);
        $this->assertEquals(SyncStatus::PENDING, $log->status);
    }

    public function test_started_at_is_cast_to_datetime(): void
    {
        $log = SyncLog::factory()->create([
            'started_at' => '2026-07-21 10:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $log->started_at);
    }

    public function test_completed_at_is_cast_to_datetime(): void
    {
        $log = SyncLog::factory()->create([
            'completed_at' => '2026-07-21 10:05:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $log->completed_at);
    }

    public function test_sync_status_enum_labels(): void
    {
        $this->assertEquals('等待中', SyncStatus::PENDING->label());
        $this->assertEquals('成功', SyncStatus::SUCCESS->label());
        $this->assertEquals('失败', SyncStatus::FAILED->label());
    }

    public function test_sync_status_enum_colors(): void
    {
        $this->assertEquals('gray', SyncStatus::PENDING->color());
        $this->assertEquals('success', SyncStatus::SUCCESS->color());
        $this->assertEquals('danger', SyncStatus::FAILED->color());
    }

    public function test_sync_log_belongs_to_employee(): void
    {
        $log = SyncLog::factory()->create();

        $this->assertNull($log->employee_id);
    }

    public function test_failed_factory_state(): void
    {
        $log = SyncLog::factory()->failed()->create();

        $this->assertEquals(SyncStatus::FAILED, $log->status);
        $this->assertNotNull($log->error_message);
    }
}
