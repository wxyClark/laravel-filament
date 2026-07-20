<?php

declare(strict_types=1);

namespace Tests\Feature\Trade;

use App\Domains\AfterSale\Enums\AfterSaleType;
use App\Domains\AfterSale\Services\AfterSaleService;
use App\Domains\Cart\Services\CartService;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Services\CatalogService;
use App\Domains\Fulfillment\Enums\FulfillmentType;
use App\Domains\Fulfillment\Services\FulfillmentService;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Services\OrderService;
use App\Domains\Payment\Enums\PaymentGatewayType;
use App\Domains\Payment\Services\PaymentService;

uses()->group('trade-integration');

test('full trade flow: entity product buy and refund', function () {
    $userId = 1001;
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'Phone', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 200, 'stock' => 5]);

    $cartService = new CartService;
    $cart = $cartService->forUser($userId);
    $cartService->addItem($cart, $sku->id, 1);

    $order = (new OrderService($cartService))->checkout($cart);
    expect($order->status)->toBe(OrderStatus::PENDING->value)
        ->and((float) $order->total_amount)->toBe(210.0); // 200 + 10 运费

    $payment = new PaymentService;
    $payment->recharge($userId, 500);
    $payment->pay($order, PaymentGatewayType::BALANCE->value);
    expect($order->fresh()->status)->toBe(OrderStatus::PAID->value);

    $fulfillment = (new FulfillmentService)->createFromOrder($order->fresh());
    $shipment = (new FulfillmentService)->ship($fulfillment, 'SF', 'SF999');
    expect($fulfillment->fresh()->status)->toBe('delivered')
        ->and($shipment->tracking_no)->toBe('SF999');

    $afterSale = (new AfterSaleService($payment))->request($order->fresh(), AfterSaleType::REFUND, 210);
    (new AfterSaleService($payment))->approve($afterSale);
    expect($afterSale->fresh()->status)->toBe('completed');
});

test('full trade flow: virtual product auto delivers code', function () {
    $userId = 1002;
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'Card', 'type' => ProductType::VIRTUAL]);
    $sku = $catalog->addSku($product, ['price' => 88, 'stock' => 100]);

    $cartService = new CartService;
    $cart = $cartService->forUser($userId);
    $cartService->addItem($cart, $sku->id, 2);

    $order = (new OrderService($cartService))->checkout($cart);
    expect((float) $order->total_amount)->toBe(176.0); // 纯虚拟无运费

    $payment = new PaymentService;
    $payment->recharge($userId, 500);
    $payment->pay($order, PaymentGatewayType::BALANCE->value);

    $fulfillment = (new FulfillmentService)->createFromOrder($order->fresh());
    expect($fulfillment->type)->toBe(FulfillmentType::VIRTUAL->value)
        ->and($fulfillment->virtualCodes->first()->plainPassword())->not->toBeEmpty();
});
