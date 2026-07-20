<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Order;

use App\Domains\Cart\Services\CartService;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Sku;
use App\Domains\Catalog\Services\CatalogService;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;

uses()->group('order');

test('checkout creates order with items and freight for entity', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 100, 'stock' => 10]);

    $cartService = new CartService;
    $cart = $cartService->forUser(10);
    $cartService->addItem($cart, $sku->id, 2);

    $order = (new OrderService($cartService))->checkout($cart);

    expect($order->status)->toBe(OrderStatus::PENDING->value)
        ->and($order->items)->toHaveCount(1)
        ->and((float) $order->total_amount)->toBe(210.0) // 200 商品 + 10 运费
        ->and((float) $order->freight)->toBe(10.0);
});

test('virtual only order has zero freight', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'V', 'type' => ProductType::VIRTUAL]);
    $sku = $catalog->addSku($product, ['price' => 50, 'stock' => 10]);

    $cartService = new CartService;
    $cart = $cartService->forUser(11);
    $cartService->addItem($cart, $sku->id, 1);

    $order = (new OrderService($cartService))->checkout($cart);

    expect((float) $order->freight)->toBe(0.0)
        ->and((float) $order->total_amount)->toBe(50.0);
});

test('checkout decrements sku stock', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 10, 'stock' => 5]);

    $cartService = new CartService;
    $cart = $cartService->forUser(12);
    $cartService->addItem($cart, $sku->id, 3);

    (new OrderService($cartService))->checkout($cart);

    expect(Sku::find($sku->id)->stock)->toBe(2);
});

test('checkout clears selected cart items', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 10, 'stock' => 5]);

    $cartService = new CartService;
    $cart = $cartService->forUser(13);
    $cartService->addItem($cart, $sku->id, 1);

    (new OrderService($cartService))->checkout($cart);

    expect($cart->items()->count())->toBe(0);
});

test('order status transition rejects invalid', function () {
    $order = Order::factory()->create(['status' => OrderStatus::COMPLETED->value]);

    $order->transitionTo(OrderStatus::PAID);
})->throws(\RuntimeException::class);

test('order paid transition sets paid_at', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PENDING->value]);

    $order->transitionTo(OrderStatus::PAID);

    expect($order->fresh()->paid_at)->not->toBeNull();
});
