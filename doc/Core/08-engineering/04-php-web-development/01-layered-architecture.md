# 01. 分层架构落地

概要：将应用分为表示层、业务逻辑层、数据访问层与持久化层，明确职责边界，提升可维护性与测试性。

最佳实践要点
- 表示层 vs 业务逻辑层：控制器仅负责请求解析、响应组装，业务规则在服务/用例中实现。
- 数据访问层应暴露接口，隐藏实现细节，便于切换 ORM/原生 SQL。
- 使用 DTO/VO 传输数据，避免直接暴露数据库实体。
- 统一异常处理与错误编码。
- 统一的日志前缀与追踪上下文。

落地要点 Demo
```php
// 数据访问层接口
interface UserRepositoryInterface {
    public function find(int $id): ?array;
}

// 数据访问实现（PDO 示例）
class UserRepository implements UserRepositoryInterface {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }
    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

// 业务逻辑层（服务）
class UserService {
    private UserRepositoryInterface $repo;
    public function __construct(UserRepositoryInterface $repo) { $this->repo = $repo; }
    public function getProfile(int $id): ?array {
        // 业务规则示例：确保邮箱存在
        $user = $this->repo->find($id);
        if ($user && isset($user['email'])) { return $user; }
        return null;
    }
}
```
