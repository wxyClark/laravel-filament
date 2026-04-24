# 安全规范：权限控制 (Gates & Policies)

## 用途说明
实现细粒度的权限控制，确保用户只能访问授权资源。

## 适用场景
- 后台管理权限
- API 资源权限
- Filament Action 权限

## 标准内容块
```markdown
## 权限控制配置

### Gate 定义
```php
// app/Providers/AuthServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        // 管理员权限
        Gate::define('admin-access', function (User $user) {
            return $user->is_admin;
        });

        // 内容管理权限
        Gate::define('manage-content', function (User $user) {
            return in_array($user->role, ['admin', 'editor']);
        });

        // 资源访问权限（带模型检查）
        Gate::define('update-post', function (User $user, $post) {
            return $user->id === $post->user_id || $user->is_admin;
        });

        Gate::define('delete-post', function (User $user, $post) {
            return $user->id === $post->user_id || $user->is_admin;
        });
    }
}
```

### Policy 定义
```php
// app/Policies/PostPolicy.php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * 用户是否可以查看列表
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 用户是否可以查看单个文章
     */
    public function view(User $user, Post $post): bool
    {
        return true;
    }

    /**
     * 用户是否可以创建文章
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'editor', 'author']);
    }

    /**
     * 用户是否可以更新文章
     */
    public function update(User $user, Post $post): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->id === $post->user_id 
            && in_array($user->role, ['editor', 'author']);
    }

    /**
     * 用户是否可以删除文章
     */
    public function delete(User $user, Post $post): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->id === $post->user_id && $user->role === 'author';
    }

    /**
     * 用户是否可以恢复软删除的文章
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->is_admin;
    }

    /**
     * 用户是否可以永久删除文章
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->is_admin;
    }
}
```

### Filament 权限集成
```php
// app/Filament/Resources/PostResource.php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage-content');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('content')
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('作者'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'published',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Post $record): bool => 
                        auth()->user()->can('update', $record)
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Post $record): bool => 
                        auth()->user()->can('delete', $record)
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete', Post::class)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
```

### 权限中间件
```php
// app/Http/Middleware/EnsureUserHasRole.php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(401);
        }

        if (! in_array($request->user()->role, $roles)) {
            abort(403, '无权访问');
        }

        return $next($request);
    }
}

// 使用方式
Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    // ...
});
```
```
```
