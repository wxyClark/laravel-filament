<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findOrFail(int $id): Product
    {
        return Product::with('skus')->findOrFail($id);
    }

    public function allActive(): Collection
    {
        return Product::active()->with('skus')->get();
    }
}
