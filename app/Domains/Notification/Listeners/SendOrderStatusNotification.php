<?php

declare(strict_types=1);

namespace App\Domains\Notification\Listeners;

use App\Domains\Notification\Events\OrderStatusChanged;
use App\Domains\Notification\Models\AdminNotification;
use App\Models\Admin;

class SendOrderStatusNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        $admins = Admin::pluck('id');

        foreach ($admins as $adminId) {
            AdminNotification::notifyOrderStatusChanged(
                adminId: $adminId,
                orderNo: $event->order->order_no,
                oldStatus: $event->oldStatus,
                newStatus: $event->newStatus,
            );
        }
    }
}
