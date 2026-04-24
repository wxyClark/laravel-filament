# 任务模板：DTO 数据转换 (Data Transfer Object)

## 用途说明
规范从 HTTP Request 到业务逻辑层的数据传递方式，实现不可变性。

## 适用场景
- 处理复杂的表单提交数据。
- 在 Service 层之间传递标准化数据结构。

## 标准内容块
```markdown
# 任务：为 {Feature} 创建 DTO

## 要求
1. **只读属性**：使用 `readonly class` 定义 DTO。
2. **静态构造器**：提供 `fromRequest(StoreXxxRequest $request)` 静态方法。
3. **类型映射**：确保所有属性都有严格的类型声明（如 `public readonly int $customerId`）。

## 示例参考
```php
readonly class OrderCreateData {
    public function __construct(
        public readonly int $customerId,
        public readonly array $items,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self {
        return new self(
            customerId: $request->user()->id,
            items: $request->validated('items'),
        );
    }
}
```
```
