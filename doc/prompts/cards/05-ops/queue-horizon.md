# 运维规范：Laravel Horizon 队列监控

## 用途说明
配置队列工作者的监控和管理，确保异步任务的可靠执行。

## 适用场景
- 邮件发送队列
- 支付回调处理
- 报表生成任务
- 文件处理任务

## 标准内容块
```markdown
## Horizon 配置

### 安装
```bash
composer require laravel/horizon
php artisan horizon:install
```

### 队列配置 (config/horizon.php)
```php
return [
    'env' => ['production', 'staging'],

    'environments' => [
        'production' => [
            'supervisor-default' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 3,
                'maxTime' => 3600,
                'maxJobs' => 500,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 90,
                'nice' => 0,
            ],
            'supervisor-payments' => [
                'connection' => 'redis',
                'queue' => ['payments', 'refunds'],
                'balance' => 'simple',
                'maxProcesses' => 5,
                'maxTime' => 3600,
                'maxJobs' => 1000,
                'memory' => 256,
                'tries' => 5,
                'timeout' => 120,
                'nice' => -10, // 高优先级
            ],
            'supervisor-notifications' => [
                'connection' => 'redis',
                'queue' => ['notifications', 'emails'],
                'balance' => 'auto',
                'maxProcesses' => 2,
                'maxTime' => 1800,
                'maxJobs' => 300,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 60,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'payments', 'notifications'],
                'balance' => 'auto',
                'maxProcesses' => 6,
                'memory' => 256,
                'tries' => 3,
            ],
        ],
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'failed' => 168,
        'monitored' => 168,
    ],
];
```

### 失败任务处理
```bash
# 查看失败任务
php artisan horizon:failed

# 重试所有失败任务
php artisan horizon:retry all

# 重试特定任务
php artisan horizon:retry {id}

# 清理旧的失败任务（保留7天）
php artisan horizon:prune --hours=168
```

### 监控指标
| 指标 | 说明 | 告警阈值 |
|------|------|---------|
| **Pending** | 等待执行的任务 | > 1000 |
| **Completed** | 已完成任务 | - |
| **Failed** | 失败任务 | > 10/小时 |
| **Processes** | 活跃进程数 | > maxProcesses |
| **Queue Wait Time** | 队列等待时间 | > 5分钟 |
| **Memory Usage** | 内存使用 | > 90% |

### 自动重启配置
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // 每天凌晨3点重启 Horizon
    $schedule->command('horizon:terminate')->dailyAt('03:00');
    
    // 清理失败任务
    $schedule->command('horizon:prune --hours=48')->daily();
}
```

### 健康检查
```php
// routes/web.php
Route::get('/health/horizon', function () {
    $status = Horizon::checkStatus();
    
    return response()->json([
        'status' => $status['status'],
        'processes' => $status['processes'],
        'queues' => $status['queues'],
    ]);
});
```
```
```
