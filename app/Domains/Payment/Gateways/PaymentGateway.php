<?php

declare(strict_types=1);

namespace App\Domains\Payment\Gateways;

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\PaymentRecord;

interface PaymentGateway
{
    public function type(): string;

    public function supports(string $gateway): bool;

    /**
     * 支付订单，成功返回流水，失败抛异常。
     */
    public function pay(Order $order): PaymentRecord;
}
