# 任务模板：API Resource 响应格式化

## 用途说明
规范 API 响应的数据格式化，实现模型数据到 API 响应的标准化转换。

## 适用场景
- RESTful API 响应格式化
- 复杂的数据结构嵌套
- 条件字段暴露

## 标准内容块
```markdown
# 任务：为 {Model} 创建 API Resource

## 角色
@{Role}

## 开发原则
1. **单一模型一个 Resource**: 每个模型对应一个 API Resource
2. **嵌套关联**: 使用 whenLoaded() 条件加载关联数据
3. **字段控制**: 区分 list 和 detail 的返回字段
4. **分页支持**: 集合类使用 ResourceCollection

## 输出格式
```php
<?php

declare(strict_types=1);

namespace App\Http\Resources\{Domain};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {Model}Resource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 基础字段...
            
            // 关联数据（条件加载）
            'category' => new CategoryResource($this->whenLoaded('category')),
            'skus' => SKUResource::collection($this->whenLoaded('skus')),
            
            // 计算字段
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
```

## 关联组件
- 上游: Model
- 下游: Controller Response
- 关联: @template-filament-resource
```

## 关联组件
- 上游: Eloquent Model
- 下游: Controller
- 关联: @template-api-resource
