<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findProductOrFail(int $id): Product
    {
        return $this->model->with('skus')->findOrFail($id);
    }

    public function allActive(): Collection
    {
        return $this->model->active()->with('skus')->get();
    }
}
