<?php

declare(strict_types=1);

namespace App\Domains\Xinfutong\Services;

use App\Domains\Xinfutong\Contracts\AccountRepositoryInterface;
use App\Domains\Xinfutong\Contracts\AccountServiceInterface;
use App\Domains\Xinfutong\Models\Account;
use App\Infrastructure\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;

class AccountService extends BaseService implements AccountServiceInterface
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepository
    ) {}

    public function create(array $data): Account
    {
        return $this->accountRepository->create($data);
    }

    public function update(Account $account, array $data): Account
    {
        return $this->accountRepository->update($account, $data);
    }

    public function delete(Account $account): bool
    {
        return $this->accountRepository->delete($account);
    }

    public function getDefault(): ?Account
    {
        return $this->accountRepository->getDefault();
    }

    public function getActive(): Collection
    {
        return $this->accountRepository->getActive();
    }

    public function setAsDefault(Account $account): void
    {
        $this->accountRepository->setAsDefault($account);
    }
}
