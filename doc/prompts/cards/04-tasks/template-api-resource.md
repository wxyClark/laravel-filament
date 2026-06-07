# 任务模板：API Resource 响应格式化 (API Resource)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
规范 API 响应的数据格式化，实现模型数据到 API 响应的标准化转换。

## 适用场景
- RESTful API 响应格式化
- 复杂的数据结构嵌套
- 条件字段暴露

## 标准内容块
```markdown
# 任务：为 {Model} 创建 API Resource

## L3: 角色设定
系统架构师确保 API 响应格式符合 RESTful 规范。

## 要求
1. **单一模型一个 Resource**：每个模型对应一个 API Resource
2. **嵌套关联**：使用 whenLoaded() 条件加载关联数据
3. **字段控制**：区分 list 和 detail 的返回字段
4. **分页支持**：集合类使用 ResourceCollection

## 🎯 设计方案（必须解释）
{字段设计、关联设计、计算字段、条件字段、性能考虑}

## 💻 代码实现
```php
<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_sn' => $this->order_sn,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

## L5: 验收标准
- [ ] 使用 whenLoaded() 条件加载
- [ ] 有对应的 ResourceCollection
- [ ] 日期格式化为 ISO 8601
- [ ] 金额字段有格式化
- [ ] 字段设计合理（不暴露敏感数据）
```
```
