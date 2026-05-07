<?php

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customer model can be instantiated', function () {
    $customer = new Customer();
    
    expect($customer)->toBeInstanceOf(Customer::class);
});

test('customer table has required columns', function () {
    // 验证列存在（通过插入数据）
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'phone' => '1234567890',
        'password' => bcrypt('password'),
    ]);
    
    expect($customer->id)->not->toBeNull();
    expect($customer->name)->toBe('Test Customer');
    expect($customer->email)->toBe('customer@example.com');
    expect($customer->phone)->toBe('1234567890');
});

test('customer email must be unique', function () {
    Customer::create([
        'name' => 'Customer 1',
        'email' => 'unique@example.com',
        'password' => bcrypt('password'),
    ]);
    
    // 尝试创建相同邮箱的客户
    Customer::create([
        'name' => 'Customer 2',
        'email' => 'unique@example.com',
        'password' => bcrypt('password'),
    ]);
})->throws(\Illuminate\Database\QueryException::class);

test('customer phone must be unique', function () {
    Customer::create([
        'name' => 'Customer 1',
        'email' => 'customer1@example.com',
        'phone' => '1234567890',
        'password' => bcrypt('password'),
    ]);
    
    // 尝试创建相同手机号的客户
    Customer::create([
        'name' => 'Customer 2',
        'email' => 'customer2@example.com',
        'phone' => '1234567890',
        'password' => bcrypt('password'),
    ]);
})->throws(\Illuminate\Database\QueryException::class);

test('customer has remember token column', function () {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'remember@example.com',
        'password' => bcrypt('password'),
    ]);
    
    // 验证 remember_token 字段存在
    expect($customer->remember_token)->toBeNull();
});

test('customer password is hashed', function () {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'hash@example.com',
        'password' => 'plain-password',
    ]);
    
    // 验证密码已哈希
    expect($customer->password)->not->toBe('plain-password');
    expect(password_verify('plain-password', $customer->password))->toBeTrue();
});

test('customer fillable attributes are correct', function () {
    $customer = new Customer();
    
    expect($customer->getFillable())->toContain('name', 'email', 'phone', 'password');
});

test('customer hidden attributes are correct', function () {
    $customer = new Customer();
    
    expect($customer->getHidden())->toContain('password', 'remember_token');
});
