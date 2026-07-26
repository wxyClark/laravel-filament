<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Xinfutong\Enums\ApprovalStatus;
use App\Domains\Xinfutong\Enums\EmployeeStatus;
use App\Domains\Xinfutong\Models\Account;
use App\Domains\Xinfutong\Models\ApprovalRequest;
use App\Domains\Xinfutong\Models\Employee;
use App\Domains\Xinfutong\Models\SyncLog;
use Illuminate\Database\Seeder;

class XinfutongDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 开始填充薪福通演示数据...');

        // 1. 创建账号配置（示例数据，需要替换为真实配置）
        $account = $this->createAccount();

        // 2. 创建员工数据
        $this->createEmployees();

        // 3. 创建同步日志
        $this->createSyncLogs($account);

        // 4. 创建审批请求
        $this->createApprovalRequests($account);

        $this->command->info('✅ 薪福通演示数据填充完成！');
        $this->command->info('');
        $this->command->info('⚠️  注意：账号配置使用的是示例数据，请在后台管理页面替换为真实配置');
        $this->command->info('   访问: http://localhost/admin → 薪福通 → 账号管理');
    }

    private function createAccount(): Account
    {
        return Account::firstOrCreate(
            ['app_id' => 'demo_app_id'],
            [
                'company_name' => '演示公司（请替换为真实企业名称）',
                'base_url' => 'https://xft.cmbchina.com/open',
                'app_id' => 'demo_app_id',
                'authority_secret' => 'demo_authority_secret',
                'sign_private_key' => $this->generateDemoPrivateKey(),
                'timeout' => 30,
                'is_default' => true,
                'is_active' => false, // 默认不启用，需要用户配置真实密钥后启用
                'remark' => '演示账号，请替换为真实配置',
            ]
        );
    }

    private function generateDemoPrivateKey(): string
    {
        // 生成一个示例私钥（仅用于演示，不能用于真实API调用）
        return <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCComwggJJAgEAAoGBALRi+MHfz...
（示例密钥，请替换为真实的SM2私钥）
-----END PRIVATE KEY-----
PEM;
    }

    private function createEmployees(): void
    {
        $employees = [
            [
                'xinfutong_id' => 'xt_emp_001',
                'employee_no' => 'EMP001',
                'name' => '张三',
                'gender' => 'male',
                'phone' => '13800138001',
                'email' => 'zhangsan@example.com',
                'department' => '技术部',
                'position' => '高级工程师',
                'hire_date' => '2020-01-15',
                'status' => EmployeeStatus::ACTIVE,
            ],
            [
                'xinfutong_id' => 'xt_emp_002',
                'employee_no' => 'EMP002',
                'name' => '李四',
                'gender' => 'female',
                'phone' => '13800138002',
                'email' => 'lisi@example.com',
                'department' => '产品部',
                'position' => '产品经理',
                'hire_date' => '2021-03-20',
                'status' => EmployeeStatus::ACTIVE,
            ],
            [
                'xinfutong_id' => 'xt_emp_003',
                'employee_no' => 'EMP003',
                'name' => '王五',
                'gender' => 'male',
                'phone' => '13800138003',
                'email' => 'wangwu@example.com',
                'department' => '财务部',
                'position' => '财务主管',
                'hire_date' => '2019-06-01',
                'status' => EmployeeStatus::ACTIVE,
            ],
            [
                'xinfutong_id' => 'xt_emp_004',
                'employee_no' => 'EMP004',
                'name' => '赵六',
                'gender' => 'female',
                'phone' => '13800138004',
                'email' => 'zhaoliu@example.com',
                'department' => '人事部',
                'position' => '人事专员',
                'hire_date' => '2022-09-01',
                'status' => EmployeeStatus::ACTIVE,
            ],
            [
                'xinfutong_id' => 'xt_emp_005',
                'employee_no' => 'EMP005',
                'name' => '孙七',
                'gender' => 'male',
                'phone' => '13800138005',
                'email' => 'sunqi@example.com',
                'department' => '技术部',
                'position' => '前端工程师',
                'hire_date' => '2023-01-10',
                'status' => EmployeeStatus::RESIGNED,
            ],
        ];

        foreach ($employees as $data) {
            Employee::firstOrCreate(
                ['xinfutong_id' => $data['xinfutong_id']],
                $data
            );
        }

        $this->command->info('  ✓ 员工: 5人');
    }

    private function createSyncLogs(Account $account): void
    {
        // 成功的同步记录
        SyncLog::create([
            'sync_type' => 'full',
            'status' => 'success',
            'records_fetched' => 5,
            'records_created' => 5,
            'records_updated' => 0,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(2)->addSeconds(30),
        ]);

        // 失败的同步记录
        SyncLog::create([
            'sync_type' => 'incremental',
            'status' => 'failed',
            'error_message' => 'API连接超时，请检查网络配置',
            'started_at' => now()->subHours(1),
            'completed_at' => now()->subHours(1),
        ]);

        $this->command->info('  ✓ 同步日志: 2条');
    }

    private function createApprovalRequests(Account $account): void
    {
        // 草稿状态的审批
        ApprovalRequest::create([
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
                'reason' => '家庭事务',
            ],
            'status' => ApprovalStatus::DRAFT,
        ]);

        // 审批中的审批
        ApprovalRequest::create([
            'approval_no' => 'AP-20260726-002',
            'approval_type' => 'expense',
            'title' => '差旅报销申请',
            'description' => '报销出差费用',
            'applicant_name' => '李四',
            'applicant_id' => 'EMP002',
            'form_data' => [
                'expense_type' => 'travel',
                'amount' => 3500.00,
                'currency' => 'CNY',
                'receipt_count' => 5,
                'description' => '上海出差3天',
            ],
            'status' => ApprovalStatus::PENDING,
            'xinfutong_approval_id' => 'xt_workflow_demo_001',
            'submitted_at' => now()->subDays(2),
        ]);

        // 已通过的审批
        ApprovalRequest::create([
            'approval_no' => 'AP-20260726-003',
            'approval_type' => 'leave',
            'title' => '病假申请',
            'description' => '申请病假1天',
            'applicant_name' => '王五',
            'applicant_id' => 'EMP003',
            'form_data' => [
                'leave_type' => 'sick',
                'start_date' => '2026-07-20',
                'end_date' => '2026-07-20',
                'days' => 1,
                'reason' => '身体不适',
            ],
            'status' => ApprovalStatus::APPROVED,
            'xinfutong_approval_id' => 'xt_workflow_demo_002',
            'submitted_at' => now()->subWeek(),
            'approved_at' => now()->subDays(5),
        ]);

        $this->command->info('  ✓ 审批请求: 3条');
    }
}
