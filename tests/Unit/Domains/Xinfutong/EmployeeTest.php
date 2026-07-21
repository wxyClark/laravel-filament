<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Xinfutong;

use App\Domains\Xinfutong\Enums\EmployeeStatus;
use App\Domains\Xinfutong\Models\Employee;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_employee(): void
    {
        $employee = Employee::factory()->create();

        $this->assertDatabaseHas('xinfutong_employees', [
            'id' => $employee->id,
            'xinfutong_id' => $employee->xinfutong_id,
        ]);
    }

    public function test_xinfutong_id_must_be_unique(): void
    {
        Employee::factory()->create(['xinfutong_id' => 'XT001']);

        $this->expectException(QueryException::class);

        Employee::factory()->create(['xinfutong_id' => 'XT001']);
    }

    public function test_status_uses_employee_status_enum(): void
    {
        $employee = Employee::factory()->create([
            'status' => EmployeeStatus::ACTIVE,
        ]);

        $this->assertInstanceOf(EmployeeStatus::class, $employee->status);
        $this->assertEquals(EmployeeStatus::ACTIVE, $employee->status);
    }

    public function test_hire_date_is_cast_to_date(): void
    {
        $employee = Employee::factory()->create([
            'hire_date' => '2026-07-21',
        ]);

        $this->assertInstanceOf(Carbon::class, $employee->hire_date);
        $this->assertEquals('2026-07-21', $employee->hire_date->format('Y-m-d'));
    }

    public function test_raw_data_is_cast_to_array(): void
    {
        $data = ['employeeId' => 'E001', 'name' => '张三'];
        $employee = Employee::factory()->create([
            'raw_data' => $data,
        ]);

        $this->assertIsArray($employee->raw_data);
        $this->assertEquals($data, $employee->raw_data);
    }

    public function test_employee_status_enum_labels(): void
    {
        $this->assertEquals('在职', EmployeeStatus::ACTIVE->label());
        $this->assertEquals('停用', EmployeeStatus::INACTIVE->label());
        $this->assertEquals('离职', EmployeeStatus::RESIGNED->label());
    }

    public function test_employee_status_enum_colors(): void
    {
        $this->assertEquals('success', EmployeeStatus::ACTIVE->color());
        $this->assertEquals('warning', EmployeeStatus::INACTIVE->color());
        $this->assertEquals('danger', EmployeeStatus::RESIGNED->color());
    }

    public function test_employee_has_sync_logs_relation(): void
    {
        $employee = Employee::factory()->create();

        $this->assertEmpty($employee->syncLogs);
    }
}
