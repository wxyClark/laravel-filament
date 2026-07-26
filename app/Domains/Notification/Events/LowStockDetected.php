<?php

declare(strict_types=1);

namespace App\Domains\Notification\Events;

use App\CrossSell\Product\Models\SKU;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockDetected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly SKU $sku,
        public readonly int $currentStock,
        public readonly int $threshold = 10,
    ) {}
}
