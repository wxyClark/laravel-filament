<?php

declare(strict_types=1);

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Enums\PaymentGatewayType;
use App\Domains\Payment\Enums\PaymentType;
use App\Domains\Payment\Models\PaymentRecord;
use App\Domains\Payment\Models\Wallet;
use App\Domains\Payment\Services\PaymentService;
use App\Models\Customer;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('可以创建支付记录', function () {
    $customer = Customer::factory()->create();

    $record = PaymentRecord::create([
        'user_id' => $customer->id,
        'order_id' => null,
        'type' => PaymentType::RECHARGE->value,
        'amount' => 100.00,
        'gateway' => PaymentGatewayType::BALANCE->value,
        'status' => 'success',
    ]);

    expect($record)->toBeInstanceOf(PaymentRecord::class);
    expect($record->amount)->toBe('100.00');
    expect($record->status)->toBe('success');
});

test('支付记录可以关联用户', function () {
    $customer = Customer::factory()->create();

    $record = PaymentRecord::create([
        'user_id' => $customer->id,
        'order_id' => null,
        'type' => PaymentType::RECHARGE->value,
        'amount' => 50.00,
        'gateway' => PaymentGatewayType::BALANCE->value,
        'status' => 'success',
    ]);

    expect($record->user)->not->toBeNull();
    expect($record->user->id)->toBe($customer->id);
});

test('支付类型枚举标签正确', function () {
    expect(PaymentType::PAY->label())->toBe('支付');
    expect(PaymentType::RECHARGE->label())->toBe('充值');
    expect(PaymentType::REFUND->label())->toBe('退款');
});

test('支付网关类型枚举标签正确', function () {
    expect(PaymentGatewayType::BALANCE->label())->toBe('余额支付');
});

test('可以创建钱包', function () {
    $customer = Customer::factory()->create();

    $wallet = Wallet::create([
        'user_id' => $customer->id,
        'balance' => 0,
    ]);

    expect($wallet)->toBeInstanceOf(Wallet::class);
    expect($wallet->balance)->toBe('0.00');
    expect($wallet->user_id)->toBe($customer->id);
});

test('钱包可以关联支付记录', function () {
    $customer = Customer::factory()->create();

    $wallet = Wallet::create([
        'user_id' => $customer->id,
        'balance' => 100.00,
    ]);

    $record = PaymentRecord::create([
        'user_id' => $customer->id,
        'order_id' => null,
        'type' => PaymentType::RECHARGE->value,
        'amount' => 100.00,
        'gateway' => PaymentGatewayType::BALANCE->value,
        'status' => 'success',
    ]);

    expect($wallet->records)->toHaveCount(1);
    expect($wallet->records->first()->id)->toBe($record->id);
});

test('PaymentService 可以充值', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $record = $service->recharge($customer->id, 100.00);

    expect($record)->toBeInstanceOf(PaymentRecord::class);
    expect($record->type)->toBe(PaymentType::RECHARGE->value);
    expect($record->amount)->toBe('100.00');
    expect($record->status)->toBe('success');

    $wallet = Wallet::where('user_id', $customer->id)->first();
    expect($wallet->balance)->toBe('100.00');
});

test('充值金额必须大于0', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $service->recharge($customer->id, 0);
})->throws(InvalidArgumentException::class, '充值金额必须大于 0');

test('充值金额不能为负数', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $service->recharge($customer->id, -50.00);
})->throws(InvalidArgumentException::class, '充值金额必须大于 0');

test('多次充值余额累加', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $service->recharge($customer->id, 100.00);
    $service->recharge($customer->id, 50.00);
    $service->recharge($customer->id, 30.00);

    $wallet = Wallet::where('user_id', $customer->id)->first();
    expect($wallet->balance)->toBe('180.00');

    $records = PaymentRecord::where('user_id', $customer->id)
        ->where('type', PaymentType::RECHARGE->value)
        ->get();
    expect($records)->toHaveCount(3);
});

test('PaymentService 可以退款', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    // 先充值
    $service->recharge($customer->id, 100.00);

    // 退款
    $record = $service->refund($customer->id, 30.00);

    expect($record)->toBeInstanceOf(PaymentRecord::class);
    expect($record->type)->toBe(PaymentType::REFUND->value);
    expect($record->amount)->toBe('30.00');
    expect($record->status)->toBe('success');

    $wallet = Wallet::where('user_id', $customer->id)->first();
    expect($wallet->balance)->toBe('130.00');
});

test('退款金额必须大于0', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $service->refund($customer->id, 0);
})->throws(InvalidArgumentException::class, '退款金额必须大于 0');

test('退款可以关联订单', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $record = $service->refund($customer->id, 50.00, 123);

    expect($record->order_id)->toBe(123);
});

test('isRefund 方法正确', function () {
    $customer = Customer::factory()->create();

    $payRecord = PaymentRecord::create([
        'user_id' => $customer->id,
        'type' => PaymentType::PAY->value,
        'amount' => 100.00,
        'gateway' => PaymentGatewayType::BALANCE->value,
        'status' => 'success',
    ]);

    $refundRecord = PaymentRecord::create([
        'user_id' => $customer->id,
        'type' => PaymentType::REFUND->value,
        'amount' => 50.00,
        'gateway' => PaymentGatewayType::BALANCE->value,
        'status' => 'success',
    ]);

    expect($payRecord->isRefund())->toBeFalse();
    expect($refundRecord->isRefund())->toBeTrue();
});

test('PaymentService 不支持的支付方式抛异常', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    // 创建一个模拟订单
    $order = Order::factory()->create([
        'status' => 'pending',
        'total_amount' => 100.00,
    ]);

    $service->pay($order, 'unsupported_gateway');
})->throws(RuntimeException::class, '不支持的支付方式');

test('充值创建支付记录', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $service->recharge($customer->id, 200.00);

    $record = PaymentRecord::where('user_id', $customer->id)
        ->where('type', PaymentType::RECHARGE->value)
        ->first();

    expect($record)->not->toBeNull();
    expect($record->gateway)->toBe(PaymentGatewayType::BALANCE->value);
    expect($record->status)->toBe('success');
});

test('退款创建支付记录', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $service->refund($customer->id, 75.00);

    $record = PaymentRecord::where('user_id', $customer->id)
        ->where('type', PaymentType::REFUND->value)
        ->first();

    expect($record)->not->toBeNull();
    expect($record->gateway)->toBe(PaymentGatewayType::BALANCE->value);
    expect($record->status)->toBe('success');
});

test('钱包余额精度正确', function () {
    $customer = Customer::factory()->create();
    $service = app(PaymentService::class);

    $service->recharge($customer->id, 99.99);
    $service->refund($customer->id, 33.33);

    $wallet = Wallet::where('user_id', $customer->id)->first();
    // 退款是增加余额：99.99 + 33.33 = 133.32
    expect($wallet->balance)->toBe('133.32');
});
