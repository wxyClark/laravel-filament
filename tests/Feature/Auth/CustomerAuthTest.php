<?php

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration page is accessible', function () {
    $response = $this->get('/register');
    
    $response->assertStatus(200);
});

test('new customers can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    
    $this->assertAuthenticated('customer');
    $response->assertRedirect('/');
});

test('email must be unique during registration', function () {
    Customer::create([
        'name' => 'Existing Customer',
        'email' => 'existing@example.com',
        'password' => bcrypt('password'),
    ]);
    
    $response = $this->post('/register', [
        'name' => 'Test Customer',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    
    $response->assertSessionHasErrors('email');
});

test('login page is accessible', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
});

test('customers can authenticate using login screen', function () {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => bcrypt('password'),
    ]);
    
    $response = $this->post('/login', [
        'email' => 'customer@example.com',
        'password' => 'password',
    ]);
    
    $this->assertAuthenticated('customer');
    $response->assertRedirect('/');
});

test('customers cannot authenticate with invalid password', function () {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => bcrypt('password'),
    ]);
    
    $response = $this->post('/login', [
        'email' => 'customer@example.com',
        'password' => 'wrong-password',
    ]);
    
    $this->assertGuest('customer');
    $response->assertSessionHasErrors('email');
});

test('customers can logout', function () {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => bcrypt('password'),
    ]);
    
    $response = $this->actingAs($customer, 'customer')->post('/logout');
    
    $this->assertGuest('customer');
    $response->assertRedirect('/');
});
