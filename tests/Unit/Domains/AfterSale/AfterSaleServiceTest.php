<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\AfterSale;

use App\Domains\AfterSale\Enums\AfterSaleType;
use App\Domains\AfterSale\Services\AfterSaleService;
use App\Domains\Cart\Services\CartService;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Sku;
use App\Domains\Catalog\Services\CatalogService;
use App\Domains\Order\Services\OrderService;
use App\Domains\Payment\Enums\PaymentGatewayType;
use App\Domains\Payment\Models\Wallet;
use App\Domains\Payment\Services\PaymentService;

uses()->group('aftersale');

function paidOrder(ProductType $type, float $price, int $userId): array
{
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'X', 'type' => $type]);
    $sku = $catalog->addSku($product, ['price' => $price, 'stock' => 10]);
    $cartService = new CartService;
    $cart = $cartService->forUser($userId);
    $cartService->addItem($cart, $sku->id, 1);
    $order = (new OrderService($cartService))->checkout($cart);
    (new PaymentService)->recharge($order->user_id, 1000);
    (new PaymentService)->pay($order, PaymentGatewayType::BALANCE->value);

    return [$order, $sku];
}

test('refund returns money to wallet', function () {
    [$order] = paidOrder(ProductType::ENTITY, 100, 50);
    $service = new AfterSaleService(new PaymentService);

    $afterSale = $service->request($order, AfterSaleType::REFUND, 100);
    $service->approve($afterSale);

    expect($afterSale->fresh()->status)->toBe('completed')
        ->and((float) Wallet::where('user_id', $order->user_id)->first()->balance)->toBe(990.0);
});

test('return refund rolls back stock', function () {
    [$order, $sku] = paidOrder(ProductType::ENTITY, 100, 51);
    $stockBefore = Sku::find($sku->id)->stock;
    $service = new AfterSaleService(new PaymentService);

    $afterSale = $service->request($order, AfterSaleType::RETURN, 100);
    $service->approve($afterSale);

    expect(Sku::find($sku->id)->stock)->toBe($stockBefore + 1);
});

test('virtual goods cannot be returned', function () {
    [$order] = paidOrder(ProductType::VIRTUAL, 50, 52);
    $service = new AfterSaleService(new PaymentService);

    $service->request($order, AfterSaleType::RETURN, 50);
})->throws(\RuntimeException::class);

test('cannot request aftersale for unpaid order', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 10, 'stock' => 10]);
    $cartService = new CartService;
    $cart = $cartService->forUser(53);
    $cartService->addItem($cart, $sku->id, 1);
    $order = (new OrderService($cartService))->checkout($cart);

    (new AfterSaleService(new PaymentService))->request($order, AfterSaleType::REFUND, 10);
})->throws(\RuntimeException::class);
