# 上下文规范：Laravel 12 最佳实践 (Laravel 12 Best Practices)

> **版本**: v3.0 | **层級**: L2 | **最后更新**: 2026-06-07

## 用途说明
确保生成的代码符合 Laravel 12 的最新语法、中间件配置和路由规范。

## 适用场景
- 创建 Controller、Middleware、Service 时
- 配置路由、中间件、服务提供者时
- 编写 API 接口时

## 标准内容块
```markdown
## Laravel 12 开发规范

### 强制要求
1. **路由定义**：使用 `bootstrap/app.php` 注册中间件，不使用 `Kernel.php`
2. **服务提供者**：使用 `bootstrap/providers.php` 注册自定义 Provider
3. **路由风格**：Web 路由使用 `web.php`，API 路由使用 `api.php`，Auth 路由使用 `auth.php`
4. **控制器**：使用构造函数注入，不使用静态方法调用 Facade

### 中间件配置 (bootstrap/app.php)
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->appendToGroup('api', [EnsureJsonApiKeyAuth::class]);
    $middleware->alias([
        'role' => EnsureUserHasRole::class,
        'permission' => EnsureUserHasPermission::class,
    ]);
})
```

### 路由定义
```php
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::resource('products', ProductController::class)
        ->except(['show'])
        ->names('products');
});
```

### 禁止做法
- ❌ 修改 `app/Http/Kernel.php`（Laravel 12 已废弃）
- ❌ 在路由中直接写匿名函数逻辑
- ❌ 使用 `Config::get()`（改用 `config()` 辅助函数）
```
```
