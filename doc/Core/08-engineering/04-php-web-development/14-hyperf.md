# 14. Hyperf 实践要点

概要：Hyperf 框架的高性能与协程特性落地实践。

落地要点
- 异步/协程编程：尽量编写协程友好代码，使用协程友好的数据库驱动与中间件。
- 路由与中间件：使用注解/属性路由，遵循职责分离。
- 数据访问：利用 Hyperf 的数据库组件、连接池与异步查询。
- 服务与依赖注入：利用 DI 容器解耦，提供清晰的契约。
- 测试：覆盖异步逻辑的单元测试与集成测试。
- 部署与运维：基于 Swoole 的服务部署、健康检查、热重载与容器化。

演示代码
```php
use Hyperf\HttpServer\Router\Router;
Router::get('/users/{id}', function ($id) {
    $service = new \App\\Service\\UserService(new \App\\Repository\\UserRepository());
    return $service->getProfile((int)$id);
});
```
