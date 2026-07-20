<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Repositories;

use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function findOrFail(int $id): Product;

    public function allActive(): Collection;
}
