# Laravel 认证方案调研与推荐报告

**调研日期**: 2026-05-01  
**项目**: laravel-filament  
**技术栈**: Laravel 12 + Filament 3.x + 前后端不分离

---

## 📊 一、Laravel 主流认证方案对比

### 1.1 方案总览

| 方案 | 类型 | 适用场景 | 复杂度 | 灵活性 |
|------|------|---------|--------|--------|
| **Laravel Breeze** | Starter Kit | 简单的前后端不分离项目 | ⭐⭐ | ⭐⭐⭐ |
| **Laravel Jetstream** | Starter Kit | 需要团队协作、双因素认证的复杂项目 | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Laravel Sanctum** | Package | API Token 认证、SPA 应用 | ⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Laravel Fortify** | Package | 自定义前端 + 后端认证逻辑 | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Filament Auth** | Built-in | Filament 后台管理 | ⭐ | ⭐⭐⭐ |

---

### 1.2 详细对比分析

#### ✅ **Laravel Breeze**（轻量级脚手架）

**特点**：
- 基于 Blade 模板引擎，前后端不分离
- 提供登录、注册、密码重置、邮箱验证等基础功能
- 使用 Tailwind CSS 和 Alpine.js
- 代码简洁，易于理解和定制

**优势**：
- ✅ 学习成本低，适合小型项目
- ✅ 完全控制前端视图
- ✅ 无额外依赖，性能优秀

**劣势**：
- ❌ 功能相对简单，缺少双因素认证、API Token 等高级功能
- ❌ 不支持多 Guard 开箱即用，需要手动配置

**适用场景**：
- 传统的前后端不分离项目
- 简单的博客、企业官网
- 不需要复杂认证逻辑的项目

---

#### ✅ **Laravel Jetstream**（全功能脚手架）

**特点**：
- 基于 Livewire 或 Inertia.js（Vue/React）
- 提供双因素认证、API Token 管理、团队管理等高级功能
- 集成 Laravel Sanctum 用于 API 认证

**优势**：
- ✅ 功能全面，开箱即用
- ✅ 支持多种前端技术栈
- ✅ 内置团队管理（Team）功能

**劣势**：
- ❌ 学习曲线陡峭
- ❌ 代码复杂度高，定制困难
- ❌ 对于简单项目来说过于臃肿

**适用场景**：
- SaaS 平台
- 需要团队协作的项目
- 需要双因素认证的企业级应用

---

#### ✅ **Laravel Sanctum**（API Token 认证）

**特点**：
- 轻量级 API 认证包
- 支持 Session 认证（SPA）和 Token 认证（Mobile/API）
- 与 Laravel 深度集成

**优势**：
- ✅ 极其轻量，几乎零配置
- ✅ 完美支持多端认证（Web、iOS、Android）
- ✅ 与 Laravel 其他认证方案无缝集成

**劣势**：
- ❌ 不提供前端视图，需要自己实现登录页面
- ❌ 不适合纯服务端渲染项目

**适用场景**：
- SPA 应用（Vue/React）
- Mobile App API
- 需要 Token 认证的混合应用

---

#### ✅ **Laravel Fortify**（认证后端逻辑）

**特点**：
- 仅提供认证后端逻辑，无前端视图
- 可自定义所有认证流程
- 与任何前端框架兼容

**优势**：
- ✅ 极高的灵活性
- ✅ 完全控制认证逻辑
- ✅ 可与任何前端方案搭配

**劣势**：
- ❌ 需要自己实现所有前端视图
- ❌ 配置相对复杂

**适用场景**：
- 需要高度定制认证流程的项目
- 已有现成前端框架的项目
- 需要特殊认证逻辑的企业应用

---

#### ✅ **Filament Auth**（后台管理认证）

**特点**：
- Filament 内置的认证系统
- 基于 Laravel 原生 Auth
- 提供美观的登录页面

**优势**：
- ✅ 零配置，开箱即用
- ✅ 与 Filament 完美集成
- ✅ 支持自定义登录页面

**劣势**：
- ❌ 仅适用于 Filament 后台
- ❌ 不适合前台用户认证

**适用场景**：
- Filament 后台管理系统
- Admin Panel 认证

---

## 🎯 二、本项目需求分析

### 2.1 项目特点

1. **前后端不分离**：使用 Blade 模板引擎
2. **双用户体系**：
   - **前台用户（Customer）**：C 端消费者，通过 H5/小程序访问
   - **后台管理员（Admin）**：B 端管理人员，通过 Filament 后台访问
3. **技术栈**：Laravel 12 + Filament 3.x
4. **架构设计**：Multi-Auth（多 Guard 隔离）

### 2.2 认证需求

#### 前台用户（Customer）
- ✅ 邮箱/手机号登录
- ✅ 密码重置
- ✅ 邮箱验证（可选）
- ✅ Session 认证（H5）或 Token 认证（小程序/API）
- ✅ 社交登录（微信/QQ，未来扩展）

#### 后台管理员（Admin）
- ✅ 邮箱登录
- ✅ 密码重置
- ✅ RBAC 权限控制（spatie/laravel-permission）
- ✅ Session 认证
- ✅ 操作日志记录

---

## 💡 三、推荐方案

### 3.1 总体策略：**混合方案**

```
前台用户（Customer） → Laravel Breeze（定制化） + Sanctum（可选）
后台管理员（Admin）  → Filament Auth（内置）
```

### 3.2 推荐理由

#### ✅ 为什么选择 Laravel Breeze 作为前台认证基础？

1. **符合项目定位**：
   - 前后端不分离，Breeze 基于 Blade，完美契合
   - 代码简洁，易于维护和定制

2. **开发效率高**：
   - 一键生成登录、注册、密码重置等视图
   - 基于 Tailwind CSS，与 Filament 设计风格统一

3. **灵活性强**：
   - 可以轻松扩展为 Multi-Auth
   - 可以集成 Sanctum 支持 API Token 认证

4. **社区支持好**：
   - Laravel 官方维护，文档完善
   - 大量第三方教程和示例

#### ✅ 为什么选择 Filament Auth 作为后台认证？

1. **零配置**：
   - Filament 已内置认证系统
   - 无需额外安装和配置

2. **完美集成**：
   - 自动处理登录、登出、密码重置
   - 与 Filament 面板无缝衔接

3. **美观大方**：
   - 提供专业的登录页面
   - 支持自定义 Logo 和品牌色

---

## 🔧 四、实施方案

### 4.1 第一阶段：安装 Laravel Breeze（前台认证）

```bash
# 1. 安装 Breeze
docker compose exec app composer require laravel/breeze --dev
docker compose exec app php artisan breeze:install blade

# 2. 运行迁移（创建 users 表和 password_reset_tokens 表）
docker compose exec app php artisan migrate

# 3. 编译前端资源
docker compose exec app npm install
docker compose exec app npm run build
```

**生成的文件**：
```
app/Http/Controllers/Auth/          # 认证控制器
resources/views/auth/               # 认证视图（登录、注册等）
routes/auth.php                     # 认证路由
database/migrations/*_users_table.php # 用户表迁移
```

---

### 4.2 第二阶段：配置 Multi-Auth（双用户体系）

#### 步骤 1：创建 Customer 和 Admin 模型

```bash
# 创建 Customer 模型
docker compose exec app php artisan make:model Customer -m

# 创建 Admin 模型
docker compose exec app php artisan make:model Admin -m
```

**`app/Models/Customer.php`**：
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 如果需要 API Token

class Customer extends Authenticatable
{
    use Notifiable, HasApiTokens; // 如果集成 Sanctum

    protected $guard = 'customer';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

**`app/Models/Admin.php`**：
```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements FilamentUser
{
    use Notifiable, HasRoles;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 判断是否可以访问 Filament 面板
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // 可以根据角色进一步限制
    }
}
```

---

#### 步骤 2：配置 `config/auth.php`

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => 'customer', // 默认 Guard 为前台用户
        'passwords' => 'customers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        // 前台用户 Guard
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],

        // 后台管理员 Guard
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        // API Token Guard（如果需要）
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'customers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        // 前台用户 Provider
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class,
        ],

        // 后台管理员 Provider
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'customers' => [
            'provider' => 'customers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];
```

---

#### 步骤 3：创建数据库迁移

**`database/migrations/xxxx_xx_xx_create_customers_table.php`**：
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
```

**`database/migrations/xxxx_xx_xx_create_admins_table.php`**：
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
```

运行迁移：
```bash
docker compose exec app php artisan migrate
```

---

### 4.3 第三阶段：配置 Filament 后台认证

#### 步骤 1：安装 Filament（如果未安装）

```bash
docker compose exec app php artisan filament:install --panels
```

#### 步骤 2：创建 Filament Panel Provider

**`app/Providers/Filament/AdminPanelProvider.php`**：
```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login() // 启用登录页面
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets::AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('admin') // 绑定 admin guard
            ->profile();
    }
}
```

#### 步骤 3：注册 Panel Provider

在 `bootstrap/app.php` 或 `config/app.php` 中注册：

```php
'providers' => [
    // ...
    App\Providers\Filament\AdminPanelProvider::class,
],
```

#### 步骤 4：创建第一个管理员用户

```bash
docker compose exec app php artisan make:filament-user
```

按提示输入：
- Name: Admin
- Email: admin@example.com
- Password: password

---

### 4.4 第四阶段：定制前台认证视图

#### 步骤 1：修改 Breeze 生成的视图以适配 Customer Guard

**`app/Http/Controllers/Auth/AuthenticatedSessionController.php`**：
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate('customer'); // 指定使用 customer guard

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout(); // 指定 guard

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

**`app/Http/Requests/Auth/LoginRequest.php`**：
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    // ... 其他代码

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(string $guard = 'customer'): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::guard($guard)->attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    // ... 其他代码
}
```

---

### 4.5 第五阶段：路由配置

**`routes/web.php`**：
```php
<?php

use Illuminate\Support\Facades\Route;

// 前台路由（Customer Guard）
Route::middleware(['auth:customer'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // 其他前台路由...
});

// 后台路由由 Filament 自动处理
```

**`routes/auth.php`**（Breeze 生成，无需修改）：
```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth:customer')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

---

## 📝 五、关键代码文件清单

### 5.1 需要创建的文件

| 文件路径 | 说明 |
|---------|------|
| `app/Models/Customer.php` | 前台用户模型 |
| `app/Models/Admin.php` | 后台管理员模型 |
| `app/Providers/Filament/AdminPanelProvider.php` | Filament 面板提供者 |
| `database/migrations/*_create_customers_table.php` | Customer 表迁移 |
| `database/migrations/*_create_admins_table.php` | Admin 表迁移 |

### 5.2 需要修改的文件

| 文件路径 | 修改内容 |
|---------|---------|
| `config/auth.php` | 配置 multi-auth guards 和 providers |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | 指定 customer guard |
| `app/Http/Requests/Auth/LoginRequest.php` | 支持自定义 guard 参数 |
| `routes/web.php` | 添加 auth:customer 中间件 |
| `bootstrap/app.php` 或 `config/app.php` | 注册 AdminPanelProvider |

### 5.3 Breeze 自动生成的文件（无需手动修改）

- `resources/views/auth/*.blade.php`（登录、注册等视图）
- `routes/auth.php`（认证路由）
- `app/Http/Controllers/Auth/*.php`（认证控制器）

---

## 🚀 六、测试验证

### 6.1 前台用户登录测试

1. 注册前台用户：
```bash
# 访问 http://localhost:8082/register
# 填写注册表单
```

2. 登录前台用户：
```bash
# 访问 http://localhost:8082/login
# 使用注册的邮箱和密码登录
```

3. 验证登录状态：
```php
// 在控制器或 Tinker 中
Auth::guard('customer')->check(); // 应返回 true
Auth::guard('customer')->user();  // 应返回 Customer 实例
```

---

### 6.2 后台管理员登录测试

1. 创建管理员用户：
```bash
docker compose exec app php artisan make:filament-user
```

2. 登录后台：
```bash
# 访问 http://localhost:8082/admin
# 使用创建的邮箱和密码登录
```

3. 验证登录状态：
```php
Auth::guard('admin')->check(); // 应返回 true
Auth::guard('admin')->user();  // 应返回 Admin 实例
```

---

## 🎯 七、方案优势总结

### 7.1 技术优势

1. **清晰的职责分离**：
   - Customer 和 Admin 物理隔离，互不干扰
   - 不同的 Guard 确保会话独立

2. **高度可扩展**：
   - 可以轻松集成 Sanctum 支持 API Token
   - 可以添加社交登录、双因素认证等高级功能

3. **符合最佳实践**：
   - 遵循 Laravel 官方推荐的 Multi-Auth 模式
   - 利用 Filament 内置认证，减少重复开发

4. **开发效率高**：
   - Breeze 提供现成的视图和控制器
   - Filament 零配置认证系统

### 7.2 业务优势

1. **安全性高**：
   - 前后端用户数据隔离
   - 独立的密码重置机制

2. **用户体验好**：
   - 前台使用现代化的 Tailwind CSS 界面
   - 后台使用专业的 Filament 管理界面

3. **维护成本低**：
   - 基于官方方案，社区支持好
   - 代码结构清晰，易于理解和维护

---

## 📚 八、参考资料

1. [Laravel Authentication Documentation](https://laravel.com/docs/12.x/authentication)
2. [Laravel Breeze Documentation](https://laravel.com/docs/12.x/starter-kits#laravel-breeze)
3. [Filament Authentication Documentation](https://filamentphp.com/docs/3.x/panels/authentication)
4. [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6/introduction)
5. [Laravel Sanctum Documentation](https://laravel.com/docs/12.x/sanctum)

---

**报告生成时间**: 2026-05-01  
**下一步**: 按照实施方案逐步执行，完成认证系统重构
