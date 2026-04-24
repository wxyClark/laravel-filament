# 运维规范：Laravel Telescope 监控配置

## 用途说明
配置本地开发和生产环境的应用监控，实现异常追踪和性能分析。

## 适用场景
- 本地开发调试
- 生产环境异常排查
- 性能瓶颈分析
- 队列任务监控

## 标准内容块
```markdown
## Telescope 监控配置

### 安装与配置
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 监控范围配置
```php
// config/telescope.php
return [
    'path' => 'telescope',
    
    'env' => ['local', 'staging'],
    
    'watchers' => [
        \Laravel\Telescope\Watchers\EntryWatcher::class => [
            'enabled' => true,
            'ignore' => [],
        ],
        \Laravel\Telescope\Watchers\QueryWatcher::class => [
            'enabled' => true,
            'ignore_packages' => true,
            'slow' => 100, // 慢查询阈值（毫秒）
        ],
        \Laravel\Telescope\Watchers\ModelWatcher::class => [
            'enabled' => true,
            'events' => ['eloquent.*'],
            'hydrations' => true,
        ],
        \Laravel\Telescope\Watchers\JobWatcher::class => [
            'enabled' => true,
            'ignore_jobs' => [],
        ],
        \Laravel\Telescope\Watchers\ExceptionWatcher::class => [
            'enabled' => true,
        ],
    ],
];
```

### 自定义过滤器
```php
// app/Providers/TelescopeServiceProvider.php
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

protected function registerWatchers(): void
{
    // 只记录特定队列的任务
    Telescope::filter(function (IncomingEntry $entry) {
        if ($entry->type === 'job') {
            return in_array($entry->content['queue'] ?? 'default', [
                'payments',
                'notifications',
                'reports',
            ]);
        }
        return true;
    });
}
```

### 性能优化建议
```php
// 生产环境采样率
Telescope::sampleRate(10); // 10% 的请求被记录

// 排除高频请求
Telescope::ignoreRequests([
    'heartbeat*',
    'health*',
]);
```

### 常用命令
```bash
# 清理旧数据（保留30天）
php artisan telescope:prune --hours=720

# 清理特定类型数据
php artisan telescope:prune --type=request --hours=24

# 导出数据
php artisan telescope:export --hours=24 > telescope-export.json
```
```
```
