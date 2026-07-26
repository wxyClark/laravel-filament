<?php

declare(strict_types=1);

use App\Domains\Xinfutong\Enums\ApprovalStatus;
use App\Domains\Xinfutong\Enums\EmployeeStatus;
use App\Domains\Xinfutong\Enums\SyncStatus;
use App\Domains\Xinfutong\Models\Account;
use App\Domains\Xinfutong\Models\ApprovalRequest;
use App\Domains\Xinfutong\Models\Employee;
use App\Domains\Xinfutong\Models\SyncLog;
use App\Domains\Xinfutong\Services\XinfutongService;
use App\Filament\Admin\Resources\XinfutongAccountResource;
use App\Filament\Admin\Resources\XinfutongApprovalResource;
use App\Filament\Admin\Resources\XinfutongEmployeeResource;
use App\Filament\Admin\Resources\XinfutongSyncLogResource;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

// ═══════════════════════════════════════════════════════════════
// 模块一：账号管理
// ═══════════════════════════════════════════════════════════════

test('可以创建薪福通账号', function () {
    $account = Account::create([
        'company_name' => '测试公司A',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'test_app_id_001',
        'authority_secret' => 'test_authority_secret',
        'sign_private_key' => 'test_private_key',
        'timeout' => 30,
        'is_default' => true,
        'is_active' => true,
    ]);

    expect($account)->toBeInstanceOf(Account::class);
    expect($account->company_name)->toBe('测试公司A');
    expect($account->app_id)->toBe('test_app_id_001');
    expect($account->is_default)->toBeTrue();
    expect($account->is_active)->toBeTrue();
});

test('可以获取默认账号', function () {
    Account::create([
        'company_name' => '默认公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'default_app_id',
        'authority_secret' => 'default_secret',
        'sign_private_key' => 'default_key',
        'is_default' => true,
        'is_active' => true,
    ]);

    Account::create([
        'company_name' => '其他公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'other_app_id',
        'authority_secret' => 'other_secret',
        'sign_private_key' => 'other_key',
        'is_default' => false,
        'is_active' => true,
    ]);

    $default = Account::getDefault();

    expect($default)->not->toBeNull();
    expect($default->company_name)->toBe('默认公司');
});

test('可以获取所有活跃账号', function () {
    Account::create([
        'company_name' => '公司A',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'app_a',
        'authority_secret' => 'secret_a',
        'sign_private_key' => 'key_a',
        'is_active' => true,
    ]);

    Account::create([
        'company_name' => '公司B',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'app_b',
        'authority_secret' => 'secret_b',
        'sign_private_key' => 'key_b',
        'is_active' => true,
    ]);

    Account::create([
        'company_name' => '停用公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'app_c',
        'authority_secret' => 'secret_c',
        'sign_private_key' => 'key_c',
        'is_active' => false,
    ]);

    $activeAccounts = Account::getActive();

    expect($activeAccounts)->toHaveCount(2);
});

test('可以设置默认账号', function () {
    $account1 = Account::create([
        'company_name' => '公司1',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'app_1',
        'authority_secret' => 'secret_1',
        'sign_private_key' => 'key_1',
        'is_default' => true,
        'is_active' => true,
    ]);

    $account2 = Account::create([
        'company_name' => '公司2',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'app_2',
        'authority_secret' => 'secret_2',
        'sign_private_key' => 'key_2',
        'is_default' => false,
        'is_active' => true,
    ]);

    $account2->setAsDefault();

    expect($account2->fresh()->is_default)->toBeTrue();
    expect($account1->fresh()->is_default)->toBeFalse();
});

test('账号隐藏敏感字段', function () {
    $account = Account::create([
        'company_name' => '测试公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'test_app_id',
        'authority_secret' => 'secret_authority',
        'sign_private_key' => 'private_key_content',
        'is_active' => true,
    ]);

    $toArray = $account->toArray();

    expect($toArray)->not->toHaveKey('sign_private_key');
    expect($toArray)->not->toHaveKey('authority_secret');
});

// ═══════════════════════════════════════════════════════════════
// 模块二：员工管理
// ═══════════════════════════════════════════════════════════════

test('可以创建员工记录', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_001',
        'employee_no' => 'EMP001',
        'name' => '张三',
        'gender' => 'male',
        'phone' => '13800138000',
        'email' => 'zhangsan@example.com',
        'department' => '技术部',
        'position' => '高级工程师',
        'hire_date' => '2020-01-15',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    expect($employee)->toBeInstanceOf(Employee::class);
    expect($employee->xinfutong_id)->toBe('xt_001');
    expect($employee->name)->toBe('张三');
    expect($employee->status)->toBe(EmployeeStatus::ACTIVE);
});

test('员工状态枚举正确', function () {
    expect(EmployeeStatus::ACTIVE->label())->toBe('在职');
    expect(EmployeeStatus::INACTIVE->label())->toBe('停用');
    expect(EmployeeStatus::RESIGNED->label())->toBe('离职');
});

test('可以更新员工信息', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_002',
        'employee_no' => 'EMP002',
        'name' => '李四',
        'department' => '产品部',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    $employee->update([
        'department' => '技术部',
        'position' => '产品经理',
    ]);

    expect($employee->fresh()->department)->toBe('技术部');
    expect($employee->fresh()->position)->toBe('产品经理');
});

test('可以按状态筛选员工', function () {
    Employee::create([
        'xinfutong_id' => 'xt_003',
        'employee_no' => 'EMP003',
        'name' => '王五',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    Employee::create([
        'xinfutong_id' => 'xt_004',
        'employee_no' => 'EMP004',
        'name' => '赵六',
        'status' => EmployeeStatus::RESIGNED,
    ]);

    $activeEmployees = Employee::where('status', EmployeeStatus::ACTIVE)->get();
    $resignedEmployees = Employee::where('status', EmployeeStatus::RESIGNED)->get();

    expect($activeEmployees)->toHaveCount(1);
    expect($resignedEmployees)->toHaveCount(1);
});

test('可以按部门筛选员工', function () {
    Employee::create([
        'xinfutong_id' => 'xt_005',
        'employee_no' => 'EMP005',
        'name' => '孙七',
        'department' => '技术部',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    Employee::create([
        'xinfutong_id' => 'xt_006',
        'employee_no' => 'EMP006',
        'name' => '周八',
        'department' => '产品部',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    $techEmployees = Employee::where('department', '技术部')->get();

    expect($techEmployees)->toHaveCount(1);
    expect($techEmployees->first()->name)->toBe('孙七');
});

// ═══════════════════════════════════════════════════════════════
// 模块三：同步日志
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

test('同步日志可以关联员工', function () {
    $employee = Employee::create([
        'xinfutong_id' => 'xt_sync_001',
        'employee_no' => 'EMP_SYNC_001',
        'name' => '同步测试员工',
        'status' => EmployeeStatus::ACTIVE,
    ]);

    $log = SyncLog::create([
        'employee_id' => $employee->id,
        'sync_type' => 'full',
        'status' => SyncStatus::PENDING,
        'started_at' => now(),
    ]);

    expect($log->employee)->not->toBeNull();
    expect($log->employee->id)->toBe($employee->id);
});

// ═══════════════════════════════════════════════════════════════
// 模块四：审批请求
// ═══════════════════════════════════════════════════════════════

test('可以创建审批请求', function () {
    $approval = ApprovalRequest::create([
        'approval_no' => 'AP-20260726-001',
        'approval_type' => 'leave',
        'title' => '年假申请',
        'description' => '申请年假3天',
        'applicant_name' => '张三',
        'applicant_id' => 'EMP001',
        'form_data' => [
            'leave_type' => 'annual',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
            'days' => 3,
        ],
        'status' => ApprovalStatus::DRAFT,
    ]);

    expect($approval)->toBeInstanceOf(ApprovalRequest::class);
    expect($approval->approval_no)->toBe('AP-20260726-001');
    expect($approval->status)->toBe(ApprovalStatus::DRAFT);
});

test('审批状态枚举正确', function () {
    expect(ApprovalStatus::DRAFT->label())->toBe('草稿');
    expect(ApprovalStatus::PENDING->label())->toBe('审批中');
    expect(ApprovalStatus::APPROVED->label())->toBe('已通过');
    expect(ApprovalStatus::REJECTED->label())->toBe('已驳回');
    expect(ApprovalStatus::WITHDRAWN->label())->toBe('已撤回');
});

test('可以更新审批状态', function () {
    $approval = ApprovalRequest::create([
        'approval_no' => 'AP-20260726-002',
        'approval_type' => 'expense',
        'title' => '报销申请',
        'description' => '报销差旅费',
        'applicant_name' => '李四',
        'applicant_id' => 'EMP002',
        'form_data' => ['amount' => 5000],
        'status' => ApprovalStatus::PENDING,
    ]);

    $approval->update([
        'status' => ApprovalStatus::APPROVED,
        'xinfutong_approval_id' => 'xt_workflow_001',
        'approved_at' => now(),
    ]);

    $fresh = $approval->fresh();

    expect($fresh->status)->toBe(ApprovalStatus::APPROVED);
    expect($fresh->xinfutong_approval_id)->toBe('xt_workflow_001');
    expect($fresh->approved_at)->not->toBeNull();
});

test('审批请求可以存储表单数据', function () {
    $approval = ApprovalRequest::create([
        'approval_no' => 'AP-20260726-003',
        'approval_type' => 'leave',
        'title' => '请假申请',
        'applicant_name' => '王五',
        'applicant_id' => 'EMP003',
        'form_data' => [
            'leave_type' => 'sick',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
            'days' => 2,
            'reason' => '身体不适',
        ],
        'status' => ApprovalStatus::DRAFT,
    ]);

    expect($approval->form_data)->toHaveKey('leave_type');
    expect($approval->form_data['days'])->toBe(2);
});

test('可以按类型筛选审批', function () {
    ApprovalRequest::create([
        'approval_no' => 'AP-LEAVE-001',
        'approval_type' => 'leave',
        'title' => '请假',
        'applicant_name' => '张三',
        'applicant_id' => 'EMP001',
        'form_data' => [],
        'status' => ApprovalStatus::PENDING,
    ]);

    ApprovalRequest::create([
        'approval_no' => 'AP-EXPENSE-001',
        'approval_type' => 'expense',
        'title' => '报销',
        'applicant_name' => '李四',
        'applicant_id' => 'EMP002',
        'form_data' => [],
        'status' => ApprovalStatus::PENDING,
    ]);

    $leaveApprovals = ApprovalRequest::where('approval_type', 'leave')->get();

    expect($leaveApprovals)->toHaveCount(1);
    expect($leaveApprovals->first()->title)->toBe('请假');
});

// ═══════════════════════════════════════════════════════════════
// 模块五：XinfutongService 服务
// ═══════════════════════════════════════════════════════════════

test('XinfutongService 可以解析账号', function () {
    $account = Account::create([
        'company_name' => '服务测试公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'service_test_app',
        'authority_secret' => 'service_test_secret',
        'sign_private_key' => 'service_test_key',
        'is_default' => true,
        'is_active' => true,
    ]);

    $service = new XinfutongService;

    expect($service->getAccount()->id)->toBe($account->id);
});

test('XinfutongService 可以通过ID解析账号', function () {
    $account = Account::create([
        'company_name' => 'ID测试公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'id_test_app',
        'authority_secret' => 'id_test_secret',
        'sign_private_key' => 'id_test_key',
        'is_active' => true,
    ]);

    $service = new XinfutongService($account->id);

    expect($service->getAccount()->id)->toBe($account->id);
});

test('XinfutongService 可以通过名称解析账号', function () {
    $account = Account::create([
        'company_name' => '名称测试公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'name_test_app',
        'authority_secret' => 'name_test_secret',
        'sign_private_key' => 'name_test_key',
        'is_active' => true,
    ]);

    $service = new XinfutongService('名称测试公司');

    expect($service->getAccount()->company_name)->toBe('名称测试公司');
});

test('XinfutongService 无账号时抛异常', function () {
    new XinfutongService;
})->throws(RuntimeException::class, '薪福通账号未配置');

test('XinfutongService 生成时间戳', function () {
    $account = Account::create([
        'company_name' => '时间戳测试公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'ts_test_app',
        'authority_secret' => 'ts_test_secret',
        'sign_private_key' => 'ts_test_key',
        'is_active' => true,
    ]);

    $service = new XinfutongService($account->id);
    $timestamp = $service->generateTimestamp();

    expect($timestamp)->toBeNumeric();
    expect(strlen($timestamp))->toBeGreaterThanOrEqual(13);
});

test('XinfutongService 生成随机数', function () {
    $account = Account::create([
        'company_name' => '随机数测试公司',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'nonce_test_app',
        'authority_secret' => 'nonce_test_secret',
        'sign_private_key' => 'nonce_test_key',
        'is_active' => true,
    ]);

    $service = new XinfutongService($account->id);
    $nonce1 = $service->generateNonce();
    $nonce2 = $service->generateNonce();

    expect($nonce1)->not->toBe($nonce2);
    expect(strlen($nonce1))->toBe(32);
});

test('XinfutongService 获取可用账号列表', function () {
    Account::create([
        'company_name' => '列表测试公司A',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'list_app_a',
        'authority_secret' => 'list_secret_a',
        'sign_private_key' => 'list_key_a',
        'is_active' => true,
    ]);

    Account::create([
        'company_name' => '列表测试公司B',
        'base_url' => 'https://xft.cmbchina.com',
        'app_id' => 'list_app_b',
        'authority_secret' => 'list_secret_b',
        'sign_private_key' => 'list_key_b',
        'is_active' => true,
    ]);

    $accounts = XinfutongService::getAvailableAccounts();

    expect($accounts)->toHaveCount(2);
    expect($accounts[0])->toHaveKeys(['id', 'company_name', 'base_url', 'app_id']);
});

// ═══════════════════════════════════════════════════════════════
// 模块六：Filament Resource 权限
// ═══════════════════════════════════════════════════════════════

test('AccountResource 有正确的权限方法', function () {
    $resource = XinfutongAccountResource::class;

    expect(method_exists($resource, 'canViewAny'))->toBeTrue();
    expect(method_exists($resource, 'canCreate'))->toBeTrue();
    expect(method_exists($resource, 'canEdit'))->toBeTrue();
    expect(method_exists($resource, 'canDelete'))->toBeTrue();
});

test('EmployeeResource 有正确的权限方法', function () {
    $resource = XinfutongEmployeeResource::class;

    expect(method_exists($resource, 'canViewAny'))->toBeTrue();
    expect(method_exists($resource, 'canCreate'))->toBeTrue();
    expect(method_exists($resource, 'canEdit'))->toBeTrue();
    expect(method_exists($resource, 'canDelete'))->toBeTrue();
});

test('ApprovalResource 有正确的权限方法', function () {
    $resource = XinfutongApprovalResource::class;

    expect(method_exists($resource, 'canViewAny'))->toBeTrue();
    expect(method_exists($resource, 'canCreate'))->toBeTrue();
    expect(method_exists($resource, 'canEdit'))->toBeTrue();
    expect(method_exists($resource, 'canDelete'))->toBeTrue();
});

test('SyncLogResource 有正确的权限方法', function () {
    $resource = XinfutongSyncLogResource::class;

    expect(method_exists($resource, 'canViewAny'))->toBeTrue();
});
