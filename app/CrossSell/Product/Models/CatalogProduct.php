<?php

declare(strict_types=1);

namespace App\CrossSell\Models;

use App\CrossSell\Database\Factories\ProductFactory;
use App\CrossSell\Enums\ProductStatus;
use App\CrossSell\Enums\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    protected $table = 'products';

    protected $fillable = [
        'name',
        'category_id',
        'type',
        'status',
        'description',
        'image',
    ];

    protected $casts = [
        'category_id' => 'integer',
    ];

    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class, 'product_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::ACTIVE->value);
    }

    public function isVirtual(): bool
    {
        return $this->type === ProductType::VIRTUAL->value;
    }

    public function isBundle(): bool
    {
        return $this->type === ProductType::BUNDLE->value;
    }

    public function typeEnum(): ProductType
    {
        return ProductType::from($this->type);
    }

    public function statusEnum(): ProductStatus
    {
        return ProductStatus::from($this->status);
    }
}
