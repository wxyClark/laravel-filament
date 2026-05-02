# 05. 参数化查询（防 SQL 注入）

概要：使用参数绑定、预处理语句来防止 SQL 注入，提升查询安全性。

最佳实践要点
- 使用 PDO 的 prepare/execute、绑定参数。
- 统一数据库访问层，避免拼接 SQL。
- 对输入进行基本验证并转义输出。

落地 Demo
```php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=test', 'user', 'pass');
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
```
