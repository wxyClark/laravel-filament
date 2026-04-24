# 安全规范：API 认证配置 (Sanctum)

## 用途说明
配置基于 Token 的 API 认证，保护接口安全。

## 适用场景
- 移动端 API 认证
- SPA 前端认证
- 第三方系统集成

## 标准内容块
```markdown
## Sanctum 配置

### 安装
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 模型配置
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

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
}
```

### Token 管理
```php
// 创建 Token
$user = User::find($userId);
$token = $user->createToken('api-token')->plainTextToken;

// 带能力限制
$token = $user->createToken('api-token', ['read', 'write'])->plainTextToken;

// 撤销特定 Token
$user->tokens()->where('name', 'api-token')->delete();

// 撤销所有 Token
$user->tokens()->delete();

// 检查 Token 能力
if ($request->user()->tokenCan('write')) {
    // 允许写操作
}
```

### 路由配置
```php
// routes/api.php
use App\Http\Controllers\Api;

Route::prefix('v1')->group(function () {
    // 公开路由
    Route::post('/login', [Api\AuthController::class, 'login']);
    Route::post('/register', [Api\AuthController::class, 'register']);
    Route::post('/forgot-password', [Api\AuthController::class, 'forgotPassword']);

    // 需要认证的路由
    Route::middleware('auth:sanctum')->group(function () {
        // 用户信息
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [Api\AuthController::class, 'logout']);
        Route::post('/logout-all', [Api\AuthController::class, 'logoutAll']);

        // 订单资源
        Route::apiResource('orders', Api\OrderController::class);
        
        // 带能力限制的路由
        Route::middleware('abilities:write')->group(function () {
            Route::post('/orders', [Api\OrderController::class, 'store']);
            Route::put('/orders/{order}', [Api\OrderController::class, 'update']);
            Route::delete('/orders/{order}', [Api\OrderController::class, 'destroy']);
        });
    });
});
```

### 认证控制器
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => '认证失败',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => '已退出登录']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => '已撤销所有会话']);
    }
}
```

### 安全建议
- Token 有效期设置合理（建议 24 小时）
- 敏感操作需要重新验证密码
- 记录 Token 创建和使用日志
- 支持设备管理和会话撤销
```
```
