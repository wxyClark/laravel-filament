<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\User\Data\CustomerCreateData;
use App\Domains\User\Data\CustomerUpdateData;
use App\Domains\User\Events\CustomerDeleted;
use App\Domains\User\Events\CustomerRegistered;
use App\Domains\User\Events\CustomerUpdated;
use App\Domains\User\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository
    ) {}

    public function register(array $data): Customer
    {
        $data = CustomerCreateData::fromArray($data);

        if ($this->customerRepository->emailExists($data->email)) {
            throw ValidationException::withMessages([
                'email' => ['邮箱已注册'],
            ]);
        }

        $customer = $this->customerRepository->create($data->toArray());

        event(new CustomerRegistered($customer));

        return $customer;
    }

    public function findById(int $id): ?Customer
    {
        return $this->customerRepository->findById($id);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $data = CustomerUpdateData::fromArray($data);
        $updateData = $data->toArray();

        if (isset($updateData['email']) && $updateData['email'] !== $customer->email) {
            if ($this->customerRepository->emailExists($updateData['email'], $customer->id)) {
                throw ValidationException::withMessages([
                    'email' => ['邮箱已被使用'],
                ]);
            }
        }

        $customer = $this->customerRepository->update($customer, $updateData);

        event(new CustomerUpdated($customer));

        return $customer;
    }

    public function delete(Customer $customer): bool
    {
        $customerId = $customer->id;
        $result = $this->customerRepository->delete($customer);

        if ($result) {
            event(new CustomerDeleted($customerId));
        }

        return $result;
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage);
    }
}
