<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Catalog;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\BundleItem;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductBundle;
use App\Domains\Catalog\Models\Sku;
use App\Domains\Catalog\Services\CatalogService;

uses()->group('catalog');

test('can create product with type', function () {
    $service = new CatalogService;

    $product = $service->createProduct([
        'name' => 'iPhone',
        'type' => ProductType::ENTITY,
    ]);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->exists)->toBeTrue()
        ->and($product->typeEnum())->toBe(ProductType::ENTITY)
        ->and($product->status)->toBe(ProductStatus::ACTIVE->value);
});

test('can add sku to product', function () {
    $service = new CatalogService;
    $product = $service->createProduct(['name' => 'Phone', 'type' => ProductType::VIRTUAL]);

    $sku = $service->addSku($product, ['price' => 99.99, 'stock' => 10, 'specs' => ['region' => 'CN']]);

    expect($sku->product_id)->toBe($product->id)
        ->and((float) $sku->price)->toBe(99.99)
        ->and($sku->stock)->toBe(10)
        ->and($sku->specs)->toBe(['region' => 'CN']);
});

test('can create bundle referencing skus', function () {
    $service = new CatalogService;
    $p = $service->createProduct(['name' => 'Base', 'type' => ProductType::ENTITY]);
    $s1 = $service->addSku($p, ['price' => 50, 'stock' => 5]);
    $s2 = $service->addSku($p, ['price' => 30, 'stock' => 5]);

    $bundle = $service->createBundle('Combo', '70.00', [
        ['sku_id' => $s1->id, 'qty' => 1],
        ['sku_id' => $s2->id, 'qty' => 1],
    ]);

    expect($bundle)->toBeInstanceOf(ProductBundle::class)
        ->and($bundle->items)->toHaveCount(2)
        ->and((float) $bundle->price)->toBe(70.0)
        ->and($bundle->totalSkuPrice())->toBe(80.0);
});

test('decrement stock throws when insufficient', function () {
    $service = new CatalogService;
    $p = $service->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $service->addSku($p, ['price' => 10, 'stock' => 3]);

    $service->decrementStock($sku, 2);
    expect(Sku::find($sku->id)->stock)->toBe(1);

    $service->decrementStock($sku, 5);
})->throws(\RuntimeException::class);

test('product soft deletes', function () {
    $service = new CatalogService;
    $product = $service->createProduct(['name' => 'Del', 'type' => ProductType::ENTITY]);

    $product->delete();

    expect(Product::find($product->id))->toBeNull()
        ->and(Product::withTrashed()->find($product->id))->not->toBeNull();
});

test('bundle item links sku', function () {
    $service = new CatalogService;
    $p = $service->createProduct(['name' => 'P', 'type' => ProductType::ENTITY]);
    $sku = $service->addSku($p, ['price' => 10, 'stock' => 5]);
    $bundle = $service->createBundle('B', '9.00', [['sku_id' => $sku->id, 'qty' => 2]]);

    $item = BundleItem::where('bundle_id', $bundle->id)->first();
    expect($item->sku_id)->toBe($sku->id)
        ->and($item->qty)->toBe(2)
        ->and($item->sku->id)->toBe($sku->id);
});
