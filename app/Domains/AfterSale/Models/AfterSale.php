<?php

declare(strict_types=1);

namespace App\Domains\AfterSale\Models;

use App\Domains\AfterSale\Database\Factories\AfterSaleFactory;
use App\Domains\AfterSale\Enums\AfterSaleStatus;
use App\Domains\AfterSale\Enums\AfterSaleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfterSale extends Model
{
    use HasFactory;

    protected $table = 'after_sales';

    protected static function newFactory(): AfterSaleFactory
    {
        return AfterSaleFactory::new();
    }

    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'status',
        'refund_amount',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'user_id' => 'integer',
        'refund_amount' => 'decimal:2',
    ];

    public function isReturn(): bool
    {
        return $this->type === AfterSaleType::RETURN->value;
    }

    public function complete(): void
    {
        $this->status = AfterSaleStatus::COMPLETED->value;
        $this->save();
    }
}
