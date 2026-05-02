# 04. 依赖注入（DI）落地

概要：通过 DI 降低耦合，提升测试性与可扩展性。演示如何在应用中引入 DI 容器，以及在控制器/服务/仓储之间注入依赖。

最佳实践要点
- 使用容器来管理对象创建与注入，减少硬编码依赖。
- 通过构造注入实现不可变依赖，便于测试。
- 对外暴露接口而非实现，方便替换实现。
- 对构造参数使用类型提示与默认值，提升鲁棒性。

落地 Demo
```php
// 容器初始化（简易示例）
class Container {
    private array $bindings = [];
    public function bind(string $abstract, callable $factory) { $this->bindings[$abstract] = $factory; }
    public function get(string $abstract) { return ($this->bindings[$abstract])($this); }
}

$container = new Container();
$container->bind(UserRepositoryInterface::class, function($c){ return new UserRepository(new PDO('sqlite::memory:')); });
$container->bind(UserService::class, function($c){ return new UserService($c->get(UserRepositoryInterface::class)); });

// 使用
$service = $container->get(UserService::class);
```
