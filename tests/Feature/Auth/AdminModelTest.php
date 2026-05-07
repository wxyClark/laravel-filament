<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin model can be instantiated', function () {
    $admin = new Admin();
    
    expect($admin)->toBeInstanceOf(Admin::class);
});

test('admin table has required columns', function () {
    // 验证列存在（通过插入数据）
    $admin = Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    
    expect($admin->id)->not->toBeNull();
    expect($admin->name)->toBe('Test Admin');
    expect($admin->email)->toBe('admin@example.com');
});

test('admin email must be unique', function () {
    Admin::create([
        'name' => 'Admin 1',
        'email' => 'unique-admin@example.com',
        'password' => bcrypt('password'),
    ]);
    
    // 尝试创建相同邮箱的管理员
    Admin::create([
        'name' => 'Admin 2',
        'email' => 'unique-admin@example.com',
        'password' => bcrypt('password'),
    ]);
})->throws(\Illuminate\Database\QueryException::class);

test('admin has remember token column', function () {
    $admin = Admin::create([
        'name' => 'Test Admin',
        'email' => 'remember-admin@example.com',
        'password' => bcrypt('password'),
    ]);
    
    // 验证 remember_token 字段存在
    expect($admin->remember_token)->toBeNull();
});

test('admin password is hashed', function () {
    $admin = Admin::create([
        'name' => 'Test Admin',
        'email' => 'hash-admin@example.com',
        'password' => 'plain-password',
    ]);
    
    // 验证密码已哈希
    expect($admin->password)->not->toBe('plain-password');
    expect(password_verify('plain-password', $admin->password))->toBeTrue();
});

test('admin fillable attributes are correct', function () {
    $admin = new Admin();
    
    expect($admin->getFillable())->toContain('name', 'email', 'password');
});

test('admin hidden attributes are correct', function () {
    $admin = new Admin();
    
    expect($admin->getHidden())->toContain('password', 'remember_token');
});

test('admin implements FilamentUser interface', function () {
    $admin = new Admin();
    
    expect($admin)->toBeInstanceOf(\Filament\Models\Contracts\FilamentUser::class);
});

test('admin can access filament panel', function () {
    $admin = Admin::create([
        'name' => 'Panel Admin',
        'email' => 'panel@example.com',
        'password' => bcrypt('password'),
    ]);
    
    // 创建一个 mock panel
    $panel = \Mockery::mock(\Filament\Panel::class);
    
    expect($admin->canAccessPanel($panel))->toBeTrue();
});
