<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Cart;

use App\Domains\Cart\Models\CartItem;
use App\Domains\Cart\Services\CartService;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Services\CatalogService;

uses()->group('cart');

test('add item merges same sku', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 10, 'stock' => 50]);

    $service = new CartService;
    $cart = $service->forUser(1);
    $service->addItem($cart, $sku->id, 2);
    $service->addItem($cart, $sku->id, 3);

    expect($cart->items)->toHaveCount(1)
        ->and(CartItem::first()->qty)->toBe(5);
});

test('add item blocks when stock insufficient', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $catalog->addSku($product, ['price' => 10, 'stock' => 1]);

    $service = new CartService;
    $cart = $service->forUser(2);
    $service->addItem($cart, $sku->id, 5);
})->throws(\RuntimeException::class);

test('selected total computes correctly', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $s1 = $catalog->addSku($product, ['price' => 10, 'stock' => 50]);
    $s2 = $catalog->addSku($product, ['price' => 20, 'stock' => 50]);

    $service = new CartService;
    $cart = $service->forUser(3);
    $i1 = $service->addItem($cart, $s1->id, 2);
    $i2 = $service->addItem($cart, $s2->id, 1);
    $service->setSelected($i2, false);

    expect($service->selectedTotal($cart))->toBe(20.0);
});

test('clear selected removes chosen items only', function () {
    $catalog = new CatalogService;
    $product = $catalog->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $s1 = $catalog->addSku($product, ['price' => 10, 'stock' => 50]);
    $s2 = $catalog->addSku($product, ['price' => 20, 'stock' => 50]);

    $service = new CartService;
    $cart = $service->forUser(4);
    $i1 = $service->addItem($cart, $s1->id, 1);
    $service->addItem($cart, $s2->id, 1);
    $service->setSelected($i1, false);

    $service->clearSelected($cart);

    expect($cart->items()->count())->toBe(1)
        ->and($cart->items()->first()->sku_id)->toBe($s1->id);
});
