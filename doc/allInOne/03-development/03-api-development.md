# 🔌 API 开发规范

> **开发阶段** | **RESTful API** | **版本管理**

---

## 📋 概述

**设计原则：**
- RESTful 风格
- 版本管理
- 统一响应格式
- 完善的错误处理

---

## 🎯 接口设计规范

### URL 设计

```yaml
# 资源命名
GET    /api/v1/products          # 获取商品列表
GET    /api/v1/products/{id}     # 获取商品详情
POST   /api/v1/products          # 创建商品
PUT    /api/v1/products/{id}     # 更新商品
DELETE /api/v1/products/{id}     # 删除商品

# 动作接口
POST   /api/v1/orders/{id}/pay   # 支付订单
POST   /api/v1/orders/{id}/cancel # 取消订单

# 嵌套资源
GET    /api/v1/products/{id}/skus  # 商品的 SKU 列表
```

### HTTP 方法

| 方法 | 说明 | 幂等性 |
|------|------|--------|
| **GET** | 获取资源 | 是 |
| **POST** | 创建资源 | 否 |
| **PUT** | 更新资源（全量） | 是 |
| **PATCH** | 更新资源（部分） | 是 |
| **DELETE** | 删除资源 | 是 |

---

## 📊 响应格式

### 成功响应

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "商品名称",
        "price": 99.99
    }
}
```

### 列表响应

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "商品1"
        },
        {
            "id": 2,
            "name": "商品2"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
}
```

### 错误响应

```json
{
    "success": false,
    "message": "验证失败",
    "errors": {
        "name": ["名称不能为空"],
        "price": ["价格必须大于0"]
    }
}
```

### 状态码

| 状态码 | 说明 | 使用场景 |
|--------|------|---------|
| **200** | OK | 成功 |
| **201** | Created | 创建成功 |
| **204** | No Content | 删除成功 |
| **400** | Bad Request | 请求错误 |
| **401** | Unauthorized | 未认证 |
| **403** | Forbidden | 无权限 |
| **404** | Not Found | 资源不存在 |
| **422** | Unprocessable Entity | 验证失败 |
| **500** | Internal Server Error | 服务器错误 |

---

## 🔧 实现规范

### 路由定义

```php
<?php
// routes/api.php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
    
    Route::prefix('orders')->group(function () {
        Route::post('{id}/pay', [OrderController::class, 'pay']);
        Route::post('{id}/cancel', [OrderController::class, 'cancel']);
    });
});
```

### Controller

```php
<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Domains\Product\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}
    
    public function index(): JsonResponse
    {
        $products = $this->productService->list();
        
        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }
    
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => '商品不存在',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }
    
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ], 201);
    }
}
```

### FormRequest

```php
<?php
declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => '商品名称不能为空',
            'category_id.exists' => '分类不存在',
            'price.min' => '价格必须大于0',
        ];
    }
}
```

### Resource

```php
<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
```

---

## 📋 检查清单

- [ ] URL 是否符合 RESTful 规范？
- [ ] HTTP 方法是否正确？
- [ ] 响应格式是否统一？
- [ ] 错误码是否准确？
- [ ] 参数验证是否完善？
- [ ] 中文错误消息是否友好？
- [ ] 是否有权限控制？
- [ ] 是否有速率限制？

---

**版本**: v1.0 | **更新日期**: 2026-04-30
