# 🧪 Pest 测试用例模板

> **L5: 验收标准层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "all"
document_type: "test_templates"
version: "1.0"
test_framework: "Pest PHP"
total_test_files: 7
```

---

## 📁 测试目录结构

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegisterTest.php
│   ├── Ecommerce/
│   │   ├── ProductTest.php
│   │   ├── CartTest.php
│   │   └── OrderTest.php
│   ├── O2O/
│   │   ├── BookingTest.php
│   │   └── CheckinTest.php
│   ├── Distribution/
│   │   ├── DistributorTest.php
│   │   └── CommissionTest.php
│   ├── RBAC/
│   │   ├── RoleTest.php
│   │   └── PermissionTest.php
│   ├── CRM/
│   │   ├── CustomerTest.php
│   │   └── OpportunityTest.php
│   ├── DRP/
│   │   ├── PurchaseOrderTest.php
│   │   └── InventoryTest.php
│   └── Finance/
│       ├── PaymentOrderTest.php
│       └── InvoiceTest.php
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── ProductTest.php
│   │   └── OrderTest.php
│   ├── Services/
│   │   ├── CartServiceTest.php
│   │   ├── CommissionServiceTest.php
│   │   └── InventoryServiceTest.php
│   └── States/
│       ├── OrderStateTest.php
│       └── PaymentOrderStateTest.php
└── Pest.php
```

---

## 🔧 Pest.php 配置

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature', 'Unit');

// 通用 Expectations
expect()->extend('toBeValid', function () {
    return $this->toBe(true);
});

expect()->extend('toBeInvalid', function () {
    return $this->toBe(false);
});

// 数据库断言
expect()->extend('toHaveInDatabase', function (string $table, array $data) {
    $this->value = Database::has($table, $data);
    return $this;
});
```

---

## 📦 电商模块测试模板

### ProductTest.php

```php
<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\User;

describe('Product Management', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can list products', function () {
        $products = Product::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    it('can create a product', function () {
        $category = Category::factory()->create();

        $data = [
            'name' => 'Test Product',
            'sku' => 'SKU-001',
            'price' => 99.99,
            'category_id' => $category->id,
            'stock' => 100,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', $data);

        $response->assertCreated();
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'SKU-001',
        ]);
    });

    it('can update a product', function () {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Updated Product',
                'price' => 199.99,
            ]);

        $response->assertOk();
        expect($product->refresh()->name)->toBe('Updated Product');
    });

    it('can delete a product', function () {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'sku', 'price']);
    });

    it('ensures SKU uniqueness', function () {
        Product::factory()->create(['sku' => 'SKU-001']);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', [
                'name' => 'Another Product',
                'sku' => 'SKU-001',
                'price' => 99.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    });
});
```

### OrderTest.php

```php
<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\States\Order\Pending;
use App\States\Order\Completed;

describe('Order Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['stock' => 100]);
    });

    it('can create an order', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 2],
                ],
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    });

    it('deducts stock when order is created', function () {
        $this->actingAs($this->user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 2],
                ],
            ]);

        expect($this->product->refresh()->stock)->toBe(98);
    });

    it('can cancel a pending order', function () {
        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertOk();
        expect($order->refresh()->status)->toBe('cancelled');
    });

    it('restores stock when order is cancelled', function () {
        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);
        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/cancel");

        expect($this->product->refresh()->stock)->toBe(100);
    });

    it('cannot cancel a completed order', function () {
        $order = Order::factory()->completed()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(400);
    });

    it('prevents overselling', function () {
        $product = Product::factory()->create(['stock' => 1]);

        $this->actingAs($this->user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])->assertStatus(422);
    });
});
```

---

## 🏪 O2O 模块测试模板

### BookingTest.php

```php
<?php

use App\Models\Booking;
use App\Models\Merchant;
use App\Models\User;
use App\States\Booking\Pending;
use App\States\Booking\Confirmed;

describe('Booking Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->merchant = Merchant::factory()->create();
    });

    it('can create a booking', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'merchant_id' => $this->merchant->id,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'booking_time' => '14:00',
                'guests' => 2,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    });

    it('can confirm a booking', function () {
        $booking = Booking::factory()->pending()->create([
            'merchant_id' => $this->merchant->id,
        ]);

        $response = $this->actingAs($this->merchant->owner)
            ->postJson("/api/bookings/{$booking->id}/confirm");

        $response->assertOk();
        expect($booking->refresh()->status)->toBe('confirmed');
    });

    it('can cancel a booking', function () {
        $booking = Booking::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertOk();
        expect($booking->refresh()->status)->toBe('cancelled');
    });

    it('prevents double booking', function () {
        Booking::factory()->confirmed()->create([
            'merchant_id' => $this->merchant->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '14:00',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'merchant_id' => $this->merchant->id,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'booking_time' => '14:00',
                'guests' => 2,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_time']);
    });
});
```

---

## 💰 分销模块测试模板

### CommissionTest.php

```php
<?php

use App\Models\Order;
use App\Models\Distributor;
use App\Models\Commission;
use App\Models\User;
use App\Services\CommissionService;

describe('Commission Calculation', function () {
    beforeEach(function () {
        $this->service = new CommissionService();
        config([
            'distribution.primary_rate' => 10,
            'distribution.secondary_rate' => 5,
        ]);
    });

    it('calculates primary commission correctly', function () {
        $distributor = Distributor::factory()->active()->create();
        $order = Order::factory()->completed()->create([
            'total_amount' => 1000,
            'distributor_id' => $distributor->id,
            'distributor_level' => 1,
        ]);

        $commission = $this->service->calculate($order);

        expect($commission->primary)->toBe(100.00);
    });

    it('calculates secondary commission correctly', function () {
        $parent = Distributor::factory()->active()->create();
        $distributor = Distributor::factory()->active()->create([
            'parent_id' => $parent->id,
        ]);
        $order = Order::factory()->completed()->create([
            'total_amount' => 1000,
            'distributor_id' => $distributor->id,
            'distributor_level' => 2,
        ]);

        $commission = $this->service->calculate($order);

        expect($commission->secondary)->toBe(50.00);
    });

    it('creates commission records after order completion', function () {
        $distributor = Distributor::factory()->active()->create();
        $order = Order::factory()->pending()->create([
            'distributor_id' => $distributor->id,
        ]);

        $order->update(['status' => 'completed']);
        event(new \App\Events\OrderCompleted($order));

        $this->assertDatabaseHas('commissions', [
            'order_id' => $order->id,
            'distributor_id' => $distributor->id,
        ]);
    });

    it('can request withdrawal', function () {
        $distributor = Distributor::factory()->active()->create();
        Commission::factory()->create([
            'distributor_id' => $distributor->id,
            'amount' => 500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($distributor->user)
            ->postJson('/api/distributor/withdrawals', [
                'amount' => 100,
            ]);

        $response->assertCreated();
    });

    it('validates minimum withdrawal amount', function () {
        config(['distribution.min_withdrawal' => 100]);

        $distributor = Distributor::factory()->active()->create();
        Commission::factory()->create([
            'distributor_id' => $distributor->id,
            'amount' => 500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($distributor->user)
            ->postJson('/api/distributor/withdrawals', [
                'amount' => 50,
            ]);

        $response->assertStatus(422);
    });
});
```

---

## 🔐 RBAC 模块测试模板

### RoleTest.php

```php
<?php

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

describe('Role Management', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can list roles', function () {
        $roles = Role::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/roles');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('can create a role with permissions', function () {
        $permissions = Permission::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/roles', [
                'name' => 'editor',
                'permissions' => $permissions->pluck('id')->toArray(),
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('roles', ['name' => 'editor']);
    });

    it('can assign role to user', function () {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/users/{$user->id}/roles", [
                'roles' => [$role->id],
            ]);

        $response->assertOk();
        expect($user->hasRole($role->name))->toBeTrue();
    });

    it('can revoke role from user', function () {
        $role = Role::factory()->create();
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/users/{$user->id}/roles/{$role->id}");

        $response->assertOk();
        expect($user->hasRole($role->name))->toBeFalse();
    });

    it('validates role name uniqueness', function () {
        Role::factory()->create(['name' => 'admin']);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/roles', ['name' => 'admin']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('prevents deleting role with users', function () {
        $role = Role::factory()->create();
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(400);
    });
});
```

---

## 👥 CRM 模块测试模板

### CustomerTest.php

```php
<?php

use App\Models\Customer;
use App\Models\User;

describe('Customer Management', function () {
    beforeEach(function () {
        $this->sales = User::factory()->sales()->create();
    });

    it('can list customers', function () {
        $customers = Customer::factory()->count(10)
            ->for($this->sales, 'assignedTo')
            ->create();

        $response = $this->actingAs($this->sales)
            ->getJson('/api/customers');

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    });

    it('can create a customer', function () {
        $response = $this->actingAs($this->sales)
            ->postJson('/api/customers', [
                'name' => 'John Doe',
                'company' => 'Acme Inc',
                'phone' => '13800138000',
                'email' => 'john@example.com',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe',
            'assigned_to' => $this->sales->id,
        ]);
    });

    it('can add follow-up record', function () {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->sales)
            ->postJson("/api/customers/{$customer->id}/follow-ups", [
                'type' => 'call',
                'content' => 'Discussed requirements',
                'next_follow_up_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('follow_ups', [
            'customer_id' => $customer->id,
            'type' => 'call',
        ]);
    });

    it('can create opportunity', function () {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->sales)
            ->postJson('/api/opportunities', [
                'customer_id' => $customer->id,
                'name' => 'Big Deal',
                'value' => 100000,
                'probability' => 50,
                'stage' => 'qualified',
            ]);

        $response->assertCreated();
    });

    it('can merge duplicate customers', function () {
        $primary = Customer::factory()->create(['name' => 'John']);
        $duplicate = Customer::factory()->create(['name' => 'John']);

        $response = $this->actingAs($this->sales)
            ->postJson("/api/customers/{$primary->id}/merge", [
                'duplicate_id' => $duplicate->id,
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing('customers', ['id' => $duplicate->id]);
    });
});
```

---

## 📦 DRP 模块测试模板

### InventoryTest.php

```php
<?php

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\InventoryService;

describe('Inventory Management', function () {
    beforeEach(function () {
        $this->warehouse = Warehouse::factory()->create();
        $this->product = Product::factory()->create();
        $this->service = new InventoryService();
        $this->admin = User::factory()->admin()->create();
    });

    it('can check inventory level', function () {
        Inventory::factory()->create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/drp/inventory', [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertOk();
    });

    it('can adjust inventory', function () {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/drp/inventory/{$inventory->id}/adjust", [
                'adjustment_type' => 'in',
                'quantity' => 10,
                'reason' => 'Stock replenishment',
            ]);

        $response->assertOk();
        expect($inventory->refresh()->quantity)->toBe(110);
    });

    it('prevents negative inventory', function () {
        $inventory = Inventory::factory()->create([
            'quantity' => 5,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/drp/inventory/{$inventory->id}/adjust", [
                'adjustment_type' => 'out',
                'quantity' => 10,
                'reason' => 'Test',
            ]);

        $response->assertStatus(422);
    });

    it('detects low stock', function () {
        $inventory = Inventory::factory()->create([
            'quantity' => 5,
            'min_stock' => 10,
        ]);

        expect($inventory->is_low_stock)->toBeTrue();
    });

    it('can create stocktaking', function () {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/drp/inventory/stocktaking', [
                'warehouse_id' => $this->warehouse->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('stocktakings', [
            'warehouse_id' => $this->warehouse->id,
            'status' => 'draft',
        ]);
    });

    it('handles concurrent inventory updates', function () {
        $inventory = Inventory::factory()->create(['quantity' => 100]);

        // Simulate concurrent updates
        $promises = [];
        for ($i = 0; $i < 10; $i++) {
            $promises[] = async(function () use ($inventory) {
                $this->service->deduct($inventory->id, 1);
            });
        }

        await($promises);

        expect($inventory->refresh()->quantity)->toBe(90);
    });
});
```

---

## 💸 财务模块测试模板

### PaymentOrderTest.php

```php
<?php

use App\Models\PaymentOrder;
use App\Models\Account;
use App\Models\User;
use App\States\PaymentOrder\Draft;
use App\States\PaymentOrder\Pending;
use App\States\PaymentOrder\Paid;

describe('Payment Order Management', function () {
    beforeEach(function () {
        $this->finance = User::factory()->finance()->create();
        $this->approver = User::factory()->approver()->create();
        $this->account = Account::factory()->create(['balance' => 10000]);
    });

    it('can create a payment order', function () {
        $response = $this->actingAs($this->finance)
            ->postJson('/api/finance/payment-orders', [
                'payee_type' => 'supplier',
                'payee_id' => 1,
                'amount' => 1000,
                'payment_method' => 'bank_transfer',
                'bank_account_id' => $this->account->id,
                'purpose' => 'Supplier payment',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('payment_orders', [
            'status' => 'draft',
            'amount' => 1000,
        ]);
    });

    it('can submit for approval', function () {
        $order = PaymentOrder::factory()->draft()->create();

        $response = $this->actingAs($this->finance)
            ->postJson("/api/finance/payment-orders/{$order->id}/submit");

        $response->assertOk();
        expect($order->refresh()->status)->toBe('pending');
    });

    it('can approve a payment order', function () {
        $order = PaymentOrder::factory()->pending()->create();

        $response = $this->actingAs($this->approver)
            ->postJson("/api/finance/payment-orders/{$order->id}/approve", [
                'action' => 'approve',
            ]);

        $response->assertOk();
        expect($order->refresh()->status)->toBe('approved');
    });

    it('can reject a payment order', function () {
        $order = PaymentOrder::factory()->pending()->create();

        $response = $this->actingAs($this->approver)
            ->postJson("/api/finance/payment-orders/{$order->id}/approve", [
                'action' => 'reject',
                'remark' => 'Insufficient documentation',
            ]);

        $response->assertOk();
        expect($order->refresh()->status)->toBe('rejected');
    });

    it('can execute payment', function () {
        $order = PaymentOrder::factory()->approved()->create([
            'amount' => 1000,
            'bank_account_id' => $this->account->id,
        ]);

        $response = $this->actingAs($this->finance)
            ->postJson("/api/finance/payment-orders/{$order->id}/pay", [
                'transaction_no' => 'TXN-001',
                'paid_at' => now()->toDateTimeString(),
            ]);

        $response->assertOk();
        expect($order->refresh()->status)->toBe('paid');
        expect($this->account->refresh()->balance)->toBe(9000);
    });

    it('validates sufficient balance', function () {
        $account = Account::factory()->create(['balance' => 100]);
        $order = PaymentOrder::factory()->approved()->create([
            'amount' => 1000,
            'bank_account_id' => $account->id,
        ]);

        $response = $this->actingAs($this->finance)
            ->postJson("/api/finance/payment-orders/{$order->id}/pay", [
                'transaction_no' => 'TXN-002',
                'paid_at' => now()->toDateTimeString(),
            ]);

        $response->assertStatus(400);
    });
});
```

---

## 📊 测试覆盖率要求

| 模块 | Feature 测试 | Unit 测试 | 覆盖率目标 |
|------|-------------|-----------|-----------|
| 电商 | 15 | 10 | 80% |
| O2O | 10 | 8 | 80% |
| 分销 | 12 | 10 | 85% |
| RBAC | 8 | 6 | 85% |
| CRM | 10 | 8 | 80% |
| DRP | 12 | 10 | 85% |
| 财务 | 15 | 12 | 90% |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
