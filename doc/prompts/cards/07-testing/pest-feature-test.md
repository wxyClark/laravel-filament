# 测试模板：Pest 功能测试

## 用途说明
编写功能测试，验证完整业务流程和 HTTP 请求。

## 适用场景
- API 接口测试
- Filament 页面测试
- 完整业务流程测试

## 标准内容块
```markdown
## Pest 功能测试模板

### 文件位置
```
tests/Feature/
├── Api/
│   ├── AuthApiTest.php
│   └── OrderApiTest.php
├── Filament/
│   └── OrderResourceTest.php
└── Livewire/
    └── OrderFormTest.php
```

### API 测试示例
```php
<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Order;
use App\Models\Product;

describe('Order API', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('GET /api/v1/orders', function () {
        it('returns paginated orders for authenticated user', function () {
            Order::factory()->count(3)->create(['user_id' => $this->user->id]);
            Order::factory()->count(2)->create(); // Other user's orders

            $response = $this->actingAs($this->user)
                ->getJson('/api/v1/orders');

            $response->assertStatus(200)
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'order_sn', 'status', 'total', 'created_at'],
                    ],
                    'meta' => ['current_page', 'per_page', 'total'],
                ]);
        });

        it('supports filtering by status', function () {
            Order::factory()->count(2)->create([
                'user_id' => $this->user->id,
                'status' => 'pending',
            ]);
            Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'completed',
            ]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/v1/orders?status=pending');

            $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/orders');

            $response->assertStatus(401);
        });
    });

    describe('POST /api/v1/orders', function () {
        beforeEach(function () {
            Product::factory()->create(['id' => 1, 'price' => 100, 'stock' => 10]);
        });

        it('creates order successfully', function () {
            $data = [
                'items' => [
                    ['product_id' => 1, 'quantity' => 2],
                ],
                'shipping_address' => [
                    'name' => 'Test User',
                    'phone' => '13800138000',
                    'address' => 'Test Address',
                ],
            ];

            $response = $this->actingAs($this->user)
                ->postJson('/api/v1/orders', $data);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => ['id', 'order_sn', 'status', 'total'],
                ]);

            $this->assertDatabaseHas('orders', [
                'user_id' => $this->user->id,
                'status' => 'pending',
            ]);

            $this->assertDatabaseHas('order_items', [
                'product_id' => 1,
                'quantity' => 2,
            ]);
        });

        it('validates required fields', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/v1/orders', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['items', 'shipping_address']);
        });

        it('validates product exists', function () {
            $data = [
                'items' => [
                    ['product_id' => 99999, 'quantity' => 1],
                ],
            ];

            $response = $this->actingAs($this->user)
                ->postJson('/api/v1/orders', $data);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.product_id']);
        });

        it('validates stock availability', function () {
            $data = [
                'items' => [
                    ['product_id' => 1, 'quantity' => 100], // Exceeds stock
                ],
            ];

            $response = $this->actingAs($this->user)
                ->postJson('/api/v1/orders', $data);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
        });
    });

    describe('DELETE /api/v1/orders/{order}', function () {
        it('allows user to cancel their pending order', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'pending',
            ]);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/orders/{$order->id}");

            $response->assertStatus(200);
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'cancelled',
            ]);
        });

        it('prevents cancellation of shipped order', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'shipped',
            ]);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/orders/{$order->id}");

            $response->assertStatus(422)
                ->assertJson(['message' => '已发货订单无法取消']);
        });

        it('prevents cancellation of other user order', function () {
            $order = Order::factory()->create(['status' => 'pending']);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/orders/{$order->id}");

            $response->assertStatus(403);
        });
    });
});
```

### Filament 页面测试示例
```php
<?php

declare(strict_types=1);

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;

describe('Order Filament Resource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->editor = User::factory()->editor()->create();
        $this->user = User::factory()->create();
    });

    describe('access control', function () {
        it('allows admin to access order list', function () {
            $this->actingAs($this->admin)
                ->get(OrderResource::getUrl('index'))
                ->assertSuccessful();
        });

        it('allows editor to access order list', function () {
            $this->actingAs($this->editor)
                ->get(OrderResource::getUrl('index'))
                ->assertSuccessful();
        });

        it('prevents regular user from accessing order list', function () {
            $this->actingAs($this->user)
                ->get(OrderResource::getUrl('index'))
                ->assertForbidden();
        });
    });

    describe('order list', function () {
        beforeEach(function () {
            $this->actingAs($this->admin);
        });

        it('displays orders in table', function () {
            Order::factory()->count(3)->create();

            $this->get(OrderResource::getUrl('index'))
                ->assertSuccessful()
                ->assertSee('Order #')
                ->assertSee('Status');
        });

        it('supports searching by order number', function () {
            $order = Order::factory()->create(['order_sn' => 'ORD-20260424-001']);

            $this->get(OrderResource::getUrl('index') . '?tableSearchQuery=ORD-20260424')
                ->assertSuccessful()
                ->assertSee('ORD-20260424-001');
        });
    });

    describe('order creation', function () {
        beforeEach(function () {
            $this->actingAs($this->admin);
        });

        it('can render create page', function () {
            $this->get(OrderResource::getUrl('create'))
                ->assertSuccessful();
        });
    });

    describe('order editing', function () {
        beforeEach(function () {
            $this->actingAs($this->admin);
        });

        it('can render edit page', function () {
            $order = Order::factory()->create();

            $this->get(OrderResource::getUrl('edit', ['record' => $order]))
                ->assertSuccessful();
        });

        it('updates order successfully', function () {
            $order = Order::factory()->create(['status' => 'pending']);

            $this->callLivewire(OrderResource::getUrl('edit', ['record' => $order]))
                ->call('save')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
            ]);
        });
    });
});
```

### Livewire 组件测试示例
```php
<?php

declare(strict_types=1);

use App\Livewire\Forms\OrderForm;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

describe('OrderForm Livewire Component', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        Product::factory()->create(['id' => 1, 'name' => 'Test Product', 'price' => 100]);
    });

    it('renders successfully', function () {
        Livewire::test(OrderForm::class)
            ->assertStatus(200);
    });

    it('validates required fields on submit', function () {
        Livewire::test(OrderForm::class)
            ->call('submit')
            ->assertHasErrors(['customerName', 'customerEmail', 'items']);
    });

    it('creates order with valid data', function () {
        Livewire::test(OrderForm::class)
            ->set('customerName', 'Test User')
            ->set('customerEmail', 'test@example.com')
            ->set('items', [['product_id' => 1, 'quantity' => 2]])
            ->call('submit')
            ->assertHasNoErrors()
            ->assertDispatched('order-created');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
        ]);
    });

    it('adds item to order', function () {
        Livewire::test(OrderForm::class)
            ->call('addItem')
            ->assertSet('items', [['product_id' => '', 'quantity' => 1]])
            ->call('addItem')
            ->assertSet('items', [
                ['product_id' => '', 'quantity' => 1],
                ['product_id' => '', 'quantity' => 1],
            ]);
    });

    it('removes item from order', function () {
        Livewire::test(OrderForm::class)
            ->set('items', [
                ['product_id' => 1, 'quantity' => 1],
                ['product_id' => 2, 'quantity' => 2],
            ])
            ->call('removeItem', 0)
            ->assertSet('items', [['product_id' => 2, 'quantity' => 2]]);
    });
});
```

### HTTP 断言方法
```php
// 状态码断言
$response->assertStatus(200);
$response->assertSuccessful();
$response->assertCreated();
$response->assertBadRequest();
$response->assertUnauthorized();
$response->assertForbidden();
$response->assertNotFound();
$response->assertValidationError();
$response->assertConflict();

// 响应结构断言
$response->assertJsonStructure([...]);
$response->assertJson([...]);
$response->assertJsonCount(3, 'data');
$response->assertJsonPath('data.0.id', 1);
$response->assertJsonMissing(['error' => true]);

// 重定向断言
$response->assertRedirect('/path');
$response->assertRedirectToRoute('route.name');
$response->assertRedirectWithSession(['key' => 'value']);

// 验证错误断言
$response->assertJsonValidationErrors(['field']);
$response->assertJsonValidationErrors(['field' => 'error message']);
$response->assertJsonValidationMissing(['field']);

// 头部断言
$response->assertHeader('X-Custom-Header', 'value');
$response->assertCookie('cookie_name', 'value');
```
```
```
