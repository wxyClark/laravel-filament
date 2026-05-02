# 10. 缓存与队列

概要：通过缓存提升读性能，使用队列异步处理任务，降低响应时间。

最佳实践要点
- 使用缓存策略（PSR-6/PSR-16）及合适的缓存驱动。
- 将耗时任务放入队列，使用后台处理。
- 设置合理的失效策略与清理计划。

落地 Demo
```php
// 简单缓存示例（PSR-16）
$cache = new \\Symfony\\Component\\Cache\\Adapter\\ArrayAdapter();
$cache->get('user_1', function () { return ['id'=>1, 'name'=>'Alice']; }, 3600);

// 队列示例（伪代码）
class SendWelcomeEmailJob {
    private int $userId;
    public function __construct(int $userId) { $this->userId = $userId; }
    public function handle() { /* 发送邮件逻辑 */ }
}
// 将任务放入队列
// Queue::push(new SendWelcomeEmailJob($userId));
```
