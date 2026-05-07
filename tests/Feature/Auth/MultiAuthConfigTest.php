<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

test('auth config has customer guard', function () {
    $guards = Config::get('auth.guards');
    
    expect($guards)->toHaveKey('customer');
    expect($guards['customer']['driver'])->toBe('session');
    expect($guards['customer']['provider'])->toBe('customers');
});

test('auth config has admin guard', function () {
    $guards = Config::get('auth.guards');
    
    expect($guards)->toHaveKey('admin');
    expect($guards['admin']['driver'])->toBe('session');
    expect($guards['admin']['provider'])->toBe('admins');
});

test('auth config has sanctum guard', function () {
    $guards = Config::get('auth.guards');
    
    expect($guards)->toHaveKey('sanctum');
    expect($guards['sanctum']['driver'])->toBe('sanctum');
    expect($guards['sanctum']['provider'])->toBe('customers');
});

test('auth config has customers provider', function () {
    $providers = Config::get('auth.providers');
    
    expect($providers)->toHaveKey('customers');
    expect($providers['customers']['driver'])->toBe('eloquent');
    expect($providers['customers']['model'])->toBe(\App\Models\Customer::class);
});

test('auth config has admins provider', function () {
    $providers = Config::get('auth.providers');
    
    expect($providers)->toHaveKey('admins');
    expect($providers['admins']['driver'])->toBe('eloquent');
    expect($providers['admins']['model'])->toBe(\App\Models\Admin::class);
});

test('auth default guard is customer', function () {
    $defaultGuard = Config::get('auth.defaults.guard');
    
    expect($defaultGuard)->toBe('customer');
});

test('auth default password broker is customers', function () {
    $defaultPassword = Config::get('auth.defaults.passwords');
    
    expect($defaultPassword)->toBe('customers');
});

test('auth has customer password reset config', function () {
    $passwords = Config::get('auth.passwords');
    
    expect($passwords)->toHaveKey('customers');
    expect($passwords['customers']['provider'])->toBe('customers');
    expect($passwords['customers']['table'])->toBe('password_reset_tokens');
    expect($passwords['customers']['expire'])->toBe(60);
});

test('auth has admin password reset config', function () {
    $passwords = Config::get('auth.passwords');
    
    expect($passwords)->toHaveKey('admins');
    expect($passwords['admins']['provider'])->toBe('admins');
    expect($passwords['admins']['table'])->toBe('password_reset_tokens');
    expect($passwords['admins']['expire'])->toBe(60);
});

test('can switch to customer guard', function () {
    $guard = Auth::guard('customer');
    
    expect($guard)->not->toBeNull();
    expect($guard->getName())->toStartWith('login_customer');
});

test('can switch to admin guard', function () {
    $guard = Auth::guard('admin');
    
    expect($guard)->not->toBeNull();
    expect($guard->getName())->toStartWith('login_admin');
});

test('password timeout is configured', function () {
    $timeout = Config::get('auth.password_timeout');
    
    expect($timeout)->toBe(10800);
});
