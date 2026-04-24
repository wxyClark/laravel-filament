# 测试模板：Pest 单元测试

## 用途说明
编写简洁的单元测试，验证单一功能的正确性。

## 适用场景
- Service 类方法测试
- Value Object 测试
- Helper 函数测试

## 标准内容块
```markdown
## Pest 单元测试模板

### 文件位置
```
tests/Unit/
├── Services/
│   └── OrderServiceTest.php
├── ValueObjects/
│   └── MoneyTest.php
└── Helpers/
    └── StrHelperTest.php
```

### Service 测试示例
```php
<?php

declare(strict_types=1);

use App\Services\OrderService;
use App\Models\Order;
use App\Models\Customer;
use App\Exceptions\InsufficientStockException;

describe('OrderService', function () {
    beforeEach(function () {
        $this->service = new OrderService();
    });

    describe('calculateTotal', function () {
        it('calculates total with single item', function () {
            $items = [['price' => 100, 'quantity' => 2]];

            $total = $this->service->calculateTotal($items);

            expect($total)->toBe(200.0);
        });

        it('calculates total with multiple items', function () {
            $items = [
                ['price' => 100, 'quantity' => 2],
                ['price' => 50, 'quantity' => 3],
            ];

            $total = $this->service->calculateTotal($items);

            expect($total)->toBe(350.0);
        });

        it('handles decimal prices correctly', function () {
            $items = [['price' => 99.99, 'quantity' => 2]];

            $total = $this->service->calculateTotal($items);

            expect($total)->toBe(199.98);
        });

        it('throws exception for empty items', function () {
            expect(fn () => $this->service->calculateTotal([]))
                ->toThrow(InvalidArgumentException::class)
                ->and(fn () => $this->service->calculateTotal([]))
                ->toThrow('Items cannot be empty');
        });

        it('throws exception for negative quantity', function () {
            $items = [['price' => 100, 'quantity' => -1]];

            expect(fn () => $this->service->calculateTotal($items))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('applyDiscount', function () {
        it('applies percentage discount correctly', function () {
            $order = Order::factory()->make(['total' => 1000]);

            $result = $this->service->applyDiscount($order, 10); // 10% off

            expect($result)->toBe(900.0);
        });

        it('applies fixed discount correctly', function () {
            $order = Order::factory()->make(['total' => 1000]);

            $result = $this->service->applyFixedDiscount($order, 150);

            expect($result)->toBe(850.0);
        });

        it('does not allow discount greater than total', function () {
            $order = Order::factory()->make(['total' => 100]);

            expect(fn () => $this->service->applyFixedDiscount($order, 150))
                ->toThrow(InvalidArgumentException::class)
                ->getMessage()->toBe('Discount cannot exceed order total');
        });
    });

    describe('createOrder', function () {
        it('creates order with valid data', function () {
            $customer = Customer::factory()->create();
            $data = [
                'customer_id' => $customer->id,
                'items' => [
                    ['product_id' => 1, 'quantity' => 2, 'price' => 100],
                ],
            ];

            $order = $this->service->createOrder($data);

            expect($order)
                ->toBeInstanceOf(Order::class)
                ->and($order->customer_id)->toBe($customer->id)
                ->and($order->total)->toBe(200.0)
                ->and($order->status)->toBe('pending');
        });

        it('throws exception when customer not found', function () {
            $data = [
                'customer_id' => 99999,
                'items' => [['product_id' => 1, 'quantity' => 1]],
            ];

            expect(fn () => $this->service->createOrder($data))
                ->toThrow(ModelNotFoundException::class);
        });
    });
});
```

### Value Object 测试示例
```php
<?php

declare(strict_types=1);

use App\ValueObjects\Money;
use App\ValueObjects\OrderSn;

describe('Money ValueObject', function () {
    describe('creation', function () {
        it('creates money with amount and currency', function () {
            $money = new Money(100.50, 'CNY');

            expect($money->amount)->toBe(100.50)
                ->and($money->currency)->toBe('CNY');
        });

        it('throws exception for negative amount', function () {
            expect(fn () => new Money(-100, 'CNY'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws exception for invalid currency', function () {
            expect(fn () => new Money(100, 'XXX'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('operations', function () {
        it('adds two money objects', function () {
            $money1 = new Money(100, 'CNY');
            $money2 = new Money(50, 'CNY');

            $result = $money1->add($money2);

            expect($result->amount)->toBe(150.0);
        });

        it('throws exception when adding different currencies', function () {
            $money1 = new Money(100, 'CNY');
            $money2 = new Money(50, 'USD');

            expect(fn () => $money1->add($money2))
                ->toThrow(InvalidArgumentException::class);
        });

        it('subtracts money correctly', function () {
            $money1 = new Money(100, 'CNY');
            $money2 = new Money(30, 'CNY');

            $result = $money1->subtract($money2);

            expect($result->amount)->toBe(70.0);
        });

        it('multiplies by factor', function () {
            $money = new Money(100, 'CNY');

            $result = $money->multiply(2.5);

            expect($result->amount)->toBe(250.0);
        });
    });

    describe('comparison', function () {
        it('compares greater than', function () {
            $money1 = new Money(100, 'CNY');
            $money2 = new Money(50, 'CNY');

            expect($money1->greaterThan($money2))->toBeTrue()
                ->and($money2->greaterThan($money1))->toBeFalse();
        });

        it('compares less than', function () {
            $money1 = new Money(50, 'CNY');
            $money2 = new Money(100, 'CNY');

            expect($money1->lessThan($money2))->toBeTrue();
        });

        it('checks equality', function () {
            $money1 = new Money(100, 'CNY');
            $money2 = new Money(100, 'CNY');

            expect($money1->equals($money2))->toBeTrue();
        });
    });
});
```

### 常用断言
```php
// 值断言
expect($value)->toBe($expected);
expect($value)->toEqual($expected); // 深度比较
expect($value)->toBeNull();
expect($value)->toBeTrue();
expect($value)->toBeFalse();
expect($value)->toBeInt();
expect($value)->toBeString();
expect($value)->toBeArray();
expect($value)->toBeInstanceOf(ClassName::class);

// 数字断言
expect($value)->toBeGreaterThan(10);
expect($value)->toBeLessThan(100);
expect($value)->toBeBetween(10, 100);

// 字符串断言
expect($value)->toContain('substring');
expect($value)->toStartWith('prefix');
expect($value)->toEndWith('suffix');
expect($value)->toMatch('/pattern/');

// 数组断言
expect($array)->toHaveCount(3);
expect($array)->toContain($item);
expect($array)->toHaveKey('key');
expect($array)->toBeEmpty();

// 异常断言
expect($fn)->toThrow(Exception::class);
expect($fn)->toThrow(new Exception('message'));
expect($fn)->toThrow(Exception::class, 'partial message');

// 数据库断言
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
$this->assertDatabaseCount('users', 5);
```
```
```
