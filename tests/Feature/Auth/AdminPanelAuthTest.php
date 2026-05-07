<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin panel provider exists', function () {
    $provider = new \App\Providers\Filament\AdminPanelProvider(app());
    
    expect($provider)->toBeInstanceOf(\App\Providers\Filament\AdminPanelProvider::class);
});

test('admin can access filament panel', function () {
    $admin = Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);
    
    expect($admin->canAccessPanel(\Filament\Panel::make()))->toBeTrue();
});

test('admin panel uses admin guard', function () {
    $panel = \Filament\Facades\Filament::getPanel('admin');
    
    expect($panel)->not->toBeNull();
    expect($panel->getAuthGuard())->toBe('admin');
});

test('admin login page is accessible', function () {
    $response = $this->get('/admin/login');
    
    $response->assertStatus(200);
});

test('unauthenticated user cannot access admin dashboard', function () {
    $response = $this->get('/admin');
    
    $response->assertRedirect('/admin/login');
});

test('authenticated admin can access admin dashboard', function () {
    $admin = Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);
    
    $response = $this->actingAs($admin, 'admin')->get('/admin');
    
    $response->assertStatus(200);
});

test('customer cannot access admin panel with admin guard', function () {
    $customer = \App\Models\Customer::create([
        'name' => 'Test Customer',
        'email' => 'customer@test.com',
        'phone' => '1234567890',
        'password' => bcrypt('password'),
    ]);
    
    // Customer should not be able to authenticate with admin guard
    $response = $this->actingAs($customer, 'admin')->get('/admin');
    
    // Should return 403 (forbidden) or redirect to login because customer is not an admin
    $response->assertStatus(403);
});
