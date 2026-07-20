<?php

declare(strict_types=1);

namespace App\Domains\Payment\Models;

use App\Domains\Payment\Database\Factories\PaymentRecordFactory;
use App\Domains\Payment\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $table = 'payment_records';

    protected static function newFactory(): PaymentRecordFactory
    {
        return PaymentRecordFactory::new();
    }

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'amount',
        'gateway',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'order_id' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function isRefund(): bool
    {
        return $this->type === PaymentType::REFUND->value;
    }
}
