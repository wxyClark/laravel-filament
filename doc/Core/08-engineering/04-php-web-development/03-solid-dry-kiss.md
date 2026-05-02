# 03. SOLID / DRY / KISS 落地

概要：通过五大设计原则（SOLID）、避免重复、保持简洁，提升代码质量和可维护性。

最佳实践要点
- 单一职责（SRP）：每个类只负责一件事。
- 开闭原则（OCP）：对扩展开放、对修改关闭。
- 里氏替换（LSP）：子类可替换父类，不破坏行为。
- 接口隔离（ISP）：小而具体的接口。
- 依赖倒置（DIP）：高层模块不应依赖底层实现。
- DRY：避免重复实现，提取共用逻辑。
- KISS：保持简单，避免不必要的抽象。

落地 Demo
```php
// SRP 示例：一个类负责用户数据模型的验证
class UserValidator {
    public function isValid(array $data): bool { return isset($data['name'], $data['email']); }
}

// SRP + DIP：服务层依赖接口而非实现
interface UserRepositoryInterface { public function find(int $id): ?array; }
class UserService {
    private UserRepositoryInterface $repo;
    public function __construct(UserRepositoryInterface $repo) { $this->repo = $repo; }
    public function getProfile(int $id): ?array { return $this->repo->find($id); }
}
```
