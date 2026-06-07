# 任务模板：测试用例编写 (Test Coverage)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
规范测试用例的编写过程，确保测试覆盖率和测试质量。

## 适用场景
- 单元测试编写
- 特性测试编写
- 场景测试编写

## 标准内容块
```markdown
# 任务：为 {Feature} 编写测试用例

## L3: 角色设定
### QA工程师 (QAEngineer)
专注测试策略、用例设计和质量保障。

## 要求
1. **测试覆盖**：覆盖正常流程、边界条件、异常情况
2. **数据准备**：使用 Factory 创建测试数据
3. **断言完整**：验证数据库状态、响应格式、业务逻辑
4. **测试独立**：每个测试用例独立运行，不依赖其他测试

## 🎯 设计方案（必须解释）
{测试范围、测试数据、断言设计、边界条件、性能考虑}

## 💻 代码实现
```php
<?php
declare(strict_types=1);

test('admin can create order', function () {
    $admin = Admin::factory()->create();
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    $response = $this->actingAs($admin)
        ->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('order.customer_id', $customer->id);

    expect(Order::where('customer_id', $customer->id))->exists();
});
```

## L5: 验收标准
- [ ] 覆盖正常流程
- [ ] 覆盖边界条件
- [ ] 覆盖异常情况
- [ ] 使用 Factory 创建测试数据
- [ ] 断言完整
- [ ] 测试独立可重复
```
```
