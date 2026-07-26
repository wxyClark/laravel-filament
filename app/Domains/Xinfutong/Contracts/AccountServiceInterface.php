<?php

declare(strict_types=1);

namespace App\Domains\Xinfutong\Contracts;

use App\Domains\Xinfutong\Models\Account;
use Illuminate\Database\Eloquent\Collection;

/**
 * 账号服务接口
 */
interface AccountServiceInterface
{
    public function create(array $data): Account;

    public function update(Account $account, array $data): Account;

    public function delete(Account $account): bool;

    public function getDefault(): ?Account;

    public function getActive(): Collection;

    public function setAsDefault(Account $account): void;
}
