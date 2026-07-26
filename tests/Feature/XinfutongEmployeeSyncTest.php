<?php

declare(strict_types=1);

use App\Domains\Xinfutong\Enums\EmployeeStatus;
use App\Domains\Xinfutong\Enums\SyncStatus;
use App\Domains\Xinfutong\Models\Account;
use App\Domains\Xinfutong\Models\Employee;
use App\Domains\Xinfutong\Models\SyncLog;
use App\Domains\Xinfutong\Services\XinfutongService;

/**
 * 薪福通员工同步功能测试
 */
beforeEach(function () {
    // 创建测试账号
    $this->account = Account::create([
        'company_name' => '同步测试公司',
        'base_url' => 'https://xft.cmbchina.com/open',
        'app_id' => 'sync_test_app_id',
        'authority_secret' => 'sync_test_authority_secret',
        'sign_private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBg...\n-----END PRIVATE KEY-----",
        'timeout' => 30,
        'is_default' => true,
        'is_active' => true,
    ]);
});

// ═══════════════════════════════════════════════════════════════
// 模块一：员工数据结构测试
// ═══════════════════════════════════════════════════════════════

test('可以创建员工记录', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_sync_001',
        'employee_no' => 'EMP_SYNC_001',
        'name' => '同步测试员工',
        'gender' => 'male',
        'phone' => '13800138001',
        'email' => 'sync@example.com',
        'department' => '技术部',
        'position' => '工程师',
        'hire_date' => '2023-01-15',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    expect($employee)->toBeInstanceOf(Employee::class);
    expect($employee->xinfutong_id)->toBe('xt_sync_001');
    expect($employee->name)->toBe('同步测试员工');
    expect($employee->status)->toBe(EmployeeStatus::ACTIVE);
});

test('员工状态枚举正确', function () {
    expect(EmployeeStatus::ACTIVE->label())->toBe('在职');
    expect(EmployeeStatus::INACTIVE->label())->toBe('停用');
    expect(EmployeeStatus::RESIGNED->label())->toBe('离职');
});

test('员工可以存储原始数据', function () {
    $rawData = [
        'employeeId' => 'xt_raw_001',
        'empNo' => 'RAW001',
        'empName' => '原始数据员工',
        'gender' => 'female',
        'mobile' => '13900139001',
        'email' => 'raw@example.com',
        'deptName' => '产品部',
        'positionName' => '产品经理',
        'entryDate' => '2022-06-01',
        'status' => 'active',
    ];

    $employee = Employee::create([
        'xinfutong_id' => 'xt_raw_001',
        'employee_no' => 'RAW001',
        'name' => '原始数据员工',
        'department' => '产品部',
        'status' => EmployeeStatus::ACTIVE,
        'raw_data' => $rawData,
    ]);

    expect($employee->raw_data)->toBeArray();
    expect($employee->raw_data['empName'])->toBe('原始数据员工');
});

// ═══════════════════════════════════════════════════════════════
// 模块二：同步日志测试
// ═══════════════════════════════════════════════════════════════

test('可以创建同步日志', function () {
    $log = SyncLog::create([
        'sync_type' => 'full',
        'status' => SyncStatus::PENDING,
        'started_at' => now(),
    ]);

    expect($log)->toBeInstanceOf(SyncLog::class);
    expect($log->sync_type)->toBe('full');
    expect($log->status)->toBe(SyncStatus::PENDING);
});

test('同步状态枚举正确', function () {
    expect(SyncStatus::PENDING->label())->toBe('等待中');
    expect(SyncStatus::SUCCESS->label())->toBe('成功');
    expect(SyncStatus::FAILED->label())->toBe('失败');
});

test('可以更新同步日志状态', function () {
    $log = SyncLog::create([
        'sync_type' => 'full',
        'status' => SyncStatus::PENDING,
        'started_at' => now(),
    ]);

    $log->update([
        'status' => SyncStatus::SUCCESS,
        'records_fetched' => 100,
        'records_created' => 80,
        'records_updated' => 20,
        'completed_at' => now(),
    ]);

    $fresh = $log->fresh();

    expect($fresh->status)->toBe(SyncStatus::SUCCESS);
    expect($fresh->records_fetched)->toBe(100);
    expect($fresh->records_created)->toBe(80);
    expect($fresh->records_updated)->toBe(20);
    expect($fresh->completed_at)->not->toBeNull();
});

// ═══════════════════════════════════════════════════════════════
// 模块三：XinfutongService 服务测试
// ═══════════════════════════════════════════════════════════════

test('XinfutongService 可以初始化', function () {
    $service = new XinfutongService($this->account->id);

    expect($service->getAccount()->id)->toBe($this->account->id);
});

test('XinfutongService 生成时间戳', function () {
    $service = new XinfutongService($this->account->id);
    $timestamp = $service->generateTimestamp();

    expect($timestamp)->toBeNumeric();
    expect(strlen($timestamp))->toBeGreaterThanOrEqual(13);
});

test('XinfutongService 生成随机数', function () {
    $service = new XinfutongService($this->account->id);
    $nonce1 = $service->generateNonce();
    $nonce2 = $service->generateNonce();

    expect($nonce1)->not->toBe($nonce2);
    expect(strlen($nonce1))->toBe(32);
});

// ═══════════════════════════════════════════════════════════════
// 模块四：员工数据映射测试
// ═══════════════════════════════════════════════════════════════

test('可以按xinfutong_id查找员工', function () {
    Employee::create([
        'xinfutong_id' => 'xt_find_001',
        'employee_no' => 'FIND001',
        'name' => '查找测试',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    $found = Employee::where('xinfutong_id', 'xt_find_001')->first();

    expect($found)->not->toBeNull();
    expect($found->name)->toBe('查找测试');
});

test('可以更新已有员工数据', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_update_001',
        'employee_no' => 'UPDATE001',
        'name' => '更新前',
        'department' => '技术部',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    // 模拟同步更新
    $employee->update([
        'name' => '更新后',
        'department' => '产品部',
        'last_synced_at' => now(),
    ]);

    $fresh = $employee->fresh();

    expect($fresh->name)->toBe('更新后');
    expect($fresh->department)->toBe('产品部');
    expect($fresh->last_synced_at)->not->toBeNull();
});

test('可以创建新员工', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_new_001',
        'employee_no' => 'NEW001',
        'name' => '新员工',
        'department' => '市场部',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    expect($employee)->toBeInstanceOf(Employee::class);
    expect($employee->xinfutong_id)->toBe('xt_new_001');
});

// ═══════════════════════════════════════════════════════════════
// 模块五：员工筛选测试
// ═══════════════════════════════════════════════════════════════

test('可以按状态筛选员工', function () {
    Employee::create([
        'xinfutong_id' => 'xt_filter_001',
        'employee_no' => 'FILTER001',
        'name' => '在职员工',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    Employee::create([
        'xinfutong_id' => 'xt_filter_002',
        'employee_no' => 'FILTER002',
        'name' => '离职员工',
        'status' => EmployeeStatus::RESIGNED,
    ]);

    $activeEmployees = Employee::where('status', EmployeeStatus::ACTIVE)->get();
    $resignedEmployees = Employee::where('status', EmployeeStatus::RESIGNED)->get();

    expect($activeEmployees)->toHaveCount(1);
    expect($resignedEmployees)->toHaveCount(1);
});

test('可以按部门筛选员工', function () {
    Employee::create([
        'xinfutong_id' => 'xt_dept_001',
        'employee_no' => 'DEPT001',
        'name' => '技术部员工',
        'department' => '技术部',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    Employee::create([
        'xinfutong_id' => 'xt_dept_002',
        'employee_no' => 'DEPT002',
        'name' => '产品部员工',
        'department' => '产品部',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    $techEmployees = Employee::where('department', '技术部')->get();

    expect($techEmployees)->toHaveCount(1);
    expect($techEmployees->first()->name)->toBe('技术部员工');
});

test('可以按姓名搜索员工', function () {
    Employee::create([
        'xinfutong_id' => 'xt_search_001',
        'employee_no' => 'SEARCH001',
        'name' => '张三丰',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    Employee::create([
        'xinfutong_id' => 'xt_search_002',
        'employee_no' => 'SEARCH002',
        'name' => '李四光',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    $searchResults = Employee::where('name', 'like', '%张%')->get();

    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->name)->toBe('张三丰');
});

// ═══════════════════════════════════════════════════════════════
// 模块六：同步数据完整性测试
// ═══════════════════════════════════════════════════════════════

test('员工数据完整性', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_complete_001',
        'employee_no' => 'COMPLETE001',
        'name' => '完整测试',
        'gender' => 'male',
        'phone' => '13800138001',
        'email' => 'complete@example.com',
        'department' => '技术部',
        'position' => '高级工程师',
        'hire_date' => '2020-01-15',
        'status' => EmployeeStatus::ACTIVE,
        'raw_data' => ['test' => 'data'],
        'last_synced_at' => now(),
    ]);

    expect($employee->xinfutong_id)->toBe('xt_complete_001');
    expect($employee->employee_no)->toBe('COMPLETE001');
    expect($employee->name)->toBe('完整测试');
    expect($employee->gender)->toBe('male');
    expect($employee->phone)->toBe('13800138001');
    expect($employee->email)->toBe('complete@example.com');
    expect($employee->department)->toBe('技术部');
    expect($employee->position)->toBe('高级工程师');
    expect($employee->hire_date->format('Y-m-d'))->toBe('2020-01-15');
    expect($employee->status)->toBe(EmployeeStatus::ACTIVE);
    expect($employee->raw_data)->toBeArray();
    expect($employee->last_synced_at)->not->toBeNull();
});

test('批量创建员工', function () {
    $employees = [];
    for ($i = 1; $i <= 10; $i++) {
        $employees[] = Employee::create([
            'xinfutong_id' => "xt_batch_{$i}",
            'employee_no' => "BATCH{$i}",
            'name' => "批量员工{$i}",
            'department' => $i % 2 === 0 ? '技术部' : '产品部',
            'status' => EmployeeStatus::ACTIVE,
        ]);
    }

    expect($employees)->toHaveCount(10);
    expect(Employee::where('xinfutong_id', 'like', 'xt_batch_%')->count())->toBe(10);
});

test('删除员工（软删除）', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_delete_001',
        'employee_no' => 'DELETE001',
        'name' => '待删除员工',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    $employee->delete();

    expect(Employee::find($employee->id))->toBeNull();
    expect(Employee::withTrashed()->find($employee->id))->not->toBeNull();
});
