<?php

declare(strict_types=1);

namespace App\Domains\User\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer
    ) {}
}
