<?php

declare(strict_types=1);

use App\Domains\User\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| CustomerService Unit Tests
|--------------------------------------------------------------------------
|
| 测试 CustomerService 的业务逻辑：注册、更新、删除
| 使用 Mock 模拟 Repository，不依赖数据库
|
*/

beforeEach(function () {
    $this->repository = mock(CustomerRepositoryInterface::class);
    $this->service = new CustomerService($this->repository);
});

describe('CustomerService', function () {

    describe('register', function () {

        test('register creates customer and returns model', function () {
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '13800138000',
                'password' => 'password123',
            ];

            $customer = Customer::factory()->make([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '13800138000',
            ]);

            $this->repository->shouldReceive('emailExists')
                ->with('test@example.com')
                ->andReturn(false);

            $this->repository->shouldReceive('create')
                ->with([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'phone' => '13800138000',
                    'password' => 'password123',
                ])
                ->andReturn($customer);

            $result = $this->service->register($data);

            expect($result)->toBeInstanceOf(Customer::class);
            expect($result->name)->toBe('Test User');
        });

        test('register throws exception for duplicate email', function () {
            $data = [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ];

            $this->repository->shouldReceive('emailExists')
                ->with('existing@example.com')
                ->andReturn(true);

            $this->service->register($data);
        })->throws(ValidationException::class);
    });

    describe('update', function () {

        test('update modifies customer and returns updated model', function () {
            $customer = Customer::factory()->make([
                'email' => 'old@example.com',
            ]);

            $data = [
                'name' => 'Updated Name',
            ];

            $updatedCustomer = Customer::factory()->make([
                'name' => 'Updated Name',
                'email' => 'old@example.com',
            ]);

            $this->repository->shouldReceive('update')
                ->with($customer, ['name' => 'Updated Name'])
                ->andReturn($updatedCustomer);

            $result = $this->service->update($customer, $data);

            expect($result->name)->toBe('Updated Name');
        });

        test('update throws exception for duplicate email', function () {
            $customer = Customer::factory()->make([
                'id' => 1,
                'email' => 'old@example.com',
            ]);

            $data = [
                'email' => 'taken@example.com',
            ];

            $this->repository->shouldReceive('emailExists')
                ->with('taken@example.com', 1)
                ->andReturn(true);

            $this->service->update($customer, $data);
        })->throws(ValidationException::class);
    });

    describe('delete', function () {

        test('delete removes customer and returns true', function () {
            $customer = Customer::factory()->make(['id' => 1]);

            $this->repository->shouldReceive('delete')
                ->with($customer)
                ->andReturn(true);

            $result = $this->service->delete($customer);

            expect($result)->toBeTrue();
        });
    });

    describe('findById', function () {

        test('findById returns customer when exists', function () {
            $customer = Customer::factory()->make(['id' => 1]);

            $this->repository->shouldReceive('findById')
                ->with(1)
                ->andReturn($customer);

            $result = $this->service->findById(1);

            expect($result)->toBeInstanceOf(Customer::class);
        });

        test('findById returns null when not exists', function () {
            $this->repository->shouldReceive('findById')
                ->with(999)
                ->andReturn(null);

            $result = $this->service->findById(999);

            expect($result)->toBeNull();
        });
    });
});
