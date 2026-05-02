# 06. 输出转义与 CSRF 防护

概要：确保输出安全、对抗 XSS，结合 CSRF 防护实现，提升应用鲁棒性。

最佳实践要点
- 输出转义：对用户输入进行 HTML 实体转义，例如 htmlspecialchars。
- CSRF 防护：使用 CSRF token 机制，表单提交附带 token。
- 最小化暴露：对外输出的 JSON/HTML 仅包含必要字段。

落地 Demo
```php
// 输出示例（避免 XSS）
echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');

// CSRF 示例（伪代码，视框架而定）
// 在表单中包含隐藏字段 csrf_token，服务端校验该 token。
```
