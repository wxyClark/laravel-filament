# 07. 密码哈希

概要：使用强哈希算法存储密码，验证时进行安全校验，避免明文存储。

最佳实践要点
- 存储使用 password_hash，验证使用 password_verify。
- 使用适当的成本参数（如 Bcrypt/Argon2）以平衡安全和性能。
- 认证相关的数据不要暴露在日志中，敏感信息脱敏。

落地 Demo
```php
$hash = password_hash('user_password', PASSWORD_DEFAULT);
if (password_verify('user_password', $hash)) {
    // 认证通过
}
```
