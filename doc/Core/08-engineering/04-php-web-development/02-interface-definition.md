# 02. 接口定义与契约落地

概要：以接口/抽象来定义契约，确保实现与调用方的解耦，便于替换实现与测试替身（mock/stub）。

最佳实践要点
- 以接口定义行为契约，避免暴露实现细节。
- 为数据访问、服务、外部 API 都定义对应接口。
- 使用返回类型和文档注释明确接口语义。
- 结合演练：在测试中替换实现以实现快速、可控的单元测试。

落地 Demo
```php
interface UserRepositoryInterface {
    public function find(int $id): ?array;
}

class UserService {
    private UserRepositoryInterface $repo;
    public function __construct(UserRepositoryInterface $repo) { $this->repo = $repo; }
    public function getProfile(int $id): ?array { return $this->repo->find($id); }
}

// 伪实现用于测试/开发替身
class InMemoryUserRepository implements UserRepositoryInterface {
    private array $users = [1 => ['id'=>1,'name'=>'Alice','email'=>'alice@example.com']];
    public function find(int $id): ?array { return $this->users[$id] ?? null; }
}
```
