<?php

declare(strict_types=1);

namespace App\Domains\User\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;

    public function findByEmail(string $email): ?Customer;

    public function create(array $data): Customer;

    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): bool;

    public function emailExists(string $email, ?int $excludeId = null): bool;

    public function paginate(int $perPage = 20): LengthAwarePaginator;
}
