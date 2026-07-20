<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Fulfillment;

use App\Domains\Cart\Services\CartService;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Services\CatalogService;
use App\Domains\Fulfillment\Enums\FulfillmentType;
use App\Domains\Fulfillment\Services\FulfillmentService;
use App\Domains\Order\Services\OrderService;
use App\Domains\Payment\Enums\PaymentGatewayType;
use App\Domains\Payment\Services\PaymentService;

uses()->group('fulfillment');

test('virtual order auto delivers code and password', function () {
    [$order] = buildPaidOrder(ProductType::VIRTUAL, 50);

    $fulfillment = (new FulfillmentService)->createFromOrder($order);

    expect($fulfillment->type)->toBe(FulfillmentType::VIRTUAL->value)
        ->and($fulfillment->status)->toBe('delivered')
        ->and($fulfillment->virtualCodes)->toHaveCount(1);

    $code = $fulfillment->virtualCodes->first();
    expect($code->code)->toStartWith('VC')
        ->and($code->plainPassword())->not->toBeEmpty();
});

test('entity order requires shipment', function () {
    [$order] = buildPaidOrder(ProductType::ENTITY, 100);

    $service = new FulfillmentService;
    $fulfillment = $service->createFromOrder($order);

    expect($fulfillment->type)->toBe(FulfillmentType::ENTITY->value)
        ->and($fulfillment->status)->toBe('pending');

    $shipment = $service->ship($fulfillment, 'SF', 'SF123456');
    expect($shipment->tracking_no)->toBe('SF123456')
        ->and($fulfillment->fresh()->status)->toBe('delivered');
});

test('resend virtual codes returns plain password', function () {
    [$order] = buildPaidOrder(ProductType::VIRTUAL, 50);

    $service = new FulfillmentService;
    $fulfillment = $service->createFromOrder($order);

    $resend = $service->resendVirtualCodes($fulfillment);
    expect($resend[0]['code'])->toBe($fulfillment->virtualCodes->first()->code)
        ->and($resend[0]['password'])->toBe($fulfillment->virtualCodes->first()->plainPassword());
});

test('cannot fulfill unpaid order', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::VIRTUAL]);
    $sku = $catalog->addSku($product, ['price' => 50, 'stock' => 10]);
    $cartService = new CartService;
    $cart = $cartService->forUser(40);
    $cartService->addItem($cart, $sku->id, 1);
    $order = (new OrderService($cartService))->checkout($cart);

    (new FulfillmentService)->createFromOrder($order);
})->throws(\RuntimeException::class);

function buildPaidOrder(ProductType $type, float $price): array
{
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'X', 'type' => $type]);
    $sku = $catalog->addSku($product, ['price' => $price, 'stock' => 10]);
    $cartService = new CartService;
    $cart = $cartService->forUser(30);
    $cartService->addItem($cart, $sku->id, 1);
    $order = (new OrderService($cartService))->checkout($cart);
    (new PaymentService)->recharge($order->user_id, 1000);
    (new PaymentService)->pay($order, PaymentGatewayType::BALANCE->value);

    return [$order];
}
