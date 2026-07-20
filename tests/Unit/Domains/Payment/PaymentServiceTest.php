<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Payment;

use App\Domains\Cart\Services\CartService;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Services\CatalogService;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;
use App\Domains\Payment\Enums\PaymentGatewayType;
use App\Domains\Payment\Models\Wallet;
use App\Domains\Payment\Services\PaymentService;

uses()->group('payment');

test('balance pay deducts wallet and marks order paid', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 100, 'stock' => 10]);

    $cartService = new CartService;
    $cart = $cartService->forUser(20);
    $cartService->addItem($cart, $sku->id, 1);

    $order = (new OrderService($cartService))->checkout($cart);

    $pay = new PaymentService;
    $pay->recharge($order->user_id, 500);

    $record = $pay->pay($order, PaymentGatewayType::BALANCE->value);

    expect((float) Wallet::where('user_id', $order->user_id)->first()->balance)->toBe(390.0)
        ->and($record->type)->toBe('pay')
        ->and(Order::find($order->id)->status)->toBe(OrderStatus::PAID->value);
});

test('balance pay fails when insufficient', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 100, 'stock' => 10]);

    $cartService = new CartService;
    $cart = $cartService->forUser(21);
    $cartService->addItem($cart, $sku->id, 1);
    $order = (new OrderService($cartService))->checkout($cart);

    (new PaymentService)->pay($order, PaymentGatewayType::BALANCE->value);
})->throws(\RuntimeException::class);

test('recharge increases balance', function () {
    $pay = new PaymentService;
    $record = $pay->recharge(22, 123.45);

    expect((float) Wallet::where('user_id', 22)->first()->balance)->toBe(123.45)
        ->and($record->type)->toBe('recharge');
});

test('refund increases balance', function () {
    $pay = new PaymentService;
    $pay->recharge(23, 100);
    $record = $pay->refund(23, 30, 999);

    expect((float) Wallet::where('user_id', 23)->first()->balance)->toBe(130.0)
        ->and($record->type)->toBe('refund');
});

test('unsupported gateway throws', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 10, 'stock' => 10]);
    $cartService = new CartService;
    $cart = $cartService->forUser(24);
    $cartService->addItem($cart, $sku->id, 1);
    $order = (new OrderService($cartService))->checkout($cart);

    (new PaymentService)->pay($order, 'alipay');
})->throws(\RuntimeException::class);
