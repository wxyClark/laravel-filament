<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;

test('admin panel provider exists', function () {
    $provider = new AdminPanelProvider(app());

    expect($provider)->toBeInstanceOf(AdminPanelProvider::class);
});

test('admin can access filament panel', function () {
    $admin = Admin::factory()->create();

    expect($admin->canAccessPanel(Panel::make()))->toBeTrue();
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
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin');

    $response->assertStatus(200);
});
