# 09. 日志与监控

概要：结构化日志、分布式追踪、指标监控，确保可观测性。

最佳实践要点
- 使用结构化日志（JSON）便于聚合与分析。
- 统一日志前缀、上下文信息（请求 id、用户 id 等）。
- 引入 OpenTelemetry/OpenTelemetry-like 跟踪与指标系统。
- 监控关键业务指标（SLA、错误率、延迟等）。

落地 Demo
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/app.log', Logger::WARNING));
$log->info('User login', ['user_id' => 123]);
```
