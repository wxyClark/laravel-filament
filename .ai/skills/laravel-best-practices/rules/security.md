# Security Best Practices

## Mass Assignment Protection

Every model must define `$fillable` (whitelist) or `$guarded` (blacklist).

Incorrect:
```php
class User extends Model
{
    protected $guarded = []; // All fields are mass assignable
}
```

Correct:
```php
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
```

Never use `$guarded = []` on models that accept user input.

## Authorize Every Action

Use policies or gates in controllers. Never skip authorization.

Incorrect:
```php
public function update(UpdatePostRequest $request, Post $post)
{
    $post->update($request->validated());
}
```

Correct:
```php
public function update(UpdatePostRequest $request, Post $post)
{
    Gate::authorize('update', $post);

    $post->update($request->validated());
}
```

Or via Form Request:

```php
public function authorize(): bool
{
    return $this->user()->can('update', $this->route('post'));
}
```

## Prevent SQL Injection

Always use parameter binding. Never interpolate user input into queries.

Incorrect:
```php
DB::select("SELECT * FROM users WHERE name = '{$request->name}'");
```

Correct:
```php
User::where('name', $request->name)->get();

// Raw expressions with bindings
User::whereRaw('LOWER(name) = ?', [strtolower($request->name)])->get();
```

## Escape Output to Prevent XSS

Use `{{ }}` for HTML escaping. Only use `{!! !!}` for trusted, pre-sanitized content.

Incorrect:
```blade
{!! $user->bio !!}
```

Correct:
```blade
{{ $user->bio }}
```

## CSRF Protection

Include `@csrf` in all POST/PUT/DELETE Blade forms. In Inertia apps, the `@csrf` directive is automatically applied.

Incorrect:
```blade
<form method="POST" action="/posts">
    <input type="text" name="title">
</form>
```

Correct:
```blade
<form method="POST" action="/posts">
    @csrf
    <input type="text" name="title">
</form>
```

## Rate Limit Auth and API Routes

Apply `throttle` middleware to authentication and API routes.

```php
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

Route::post('/login', LoginController::class)->middleware('throttle:login');
```

## Validate File Uploads

Validate extension, MIME type, and size. The `mimes` rule checks extensions; use `mimetypes` for actual MIME type validation. Never trust client-provided filenames.

```php
public function rules(): array
{
    return [
        'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ];
}
```

Store with generated filenames:

```php
$path = $request->file('avatar')->store('avatars', 'public');
```

## Keep Secrets Out of Code

Never commit `.env`. Access secrets via `config()` only.

Incorrect:
```php
$key = env('API_KEY');
```

Correct:
```php
// config/services.php
'api_key' => env('API_KEY'),

// In application code
$key = config('services.api_key');
```

## Audit Dependencies

Run `composer audit` periodically to check for known vulnerabilities in dependencies. Automate this in CI to catch issues before deployment.

```bash
composer audit
```

## Encrypt Sensitive Database Fields

Use `encrypted` cast for API keys/tokens and mark the attribute as `hidden`.

Incorrect:
```php
class Integration extends Model
{
    protected function casts(): array
    {
        return [
            'api_key' => 'string',
        ];
    }
}
```

Correct:
```php
class Integration extends Model
{
    protected $hidden = ['api_key', 'api_secret'];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
        ];
    }
}
```

## Path Traversal Prevention (Project-Specific)

Any file download endpoint MUST validate the resolved path stays within the allowed directory. Never trust user-supplied path segments.

Incorrect:
```php
public function download(string $filename)
{
    $path = storage_path("app/exports/{$filename}");
    return response()->download($path);  // Vulnerable to ../../etc/passwd
}
```

Correct:
```php
public function download(string $filename)
{
    $path = storage_path("app/exports/{$filename}");
    $realPath = realpath($path);
    $allowedDir = realpath(storage_path('app/exports'));

    if (! $realPath || ! str_starts_with($realPath, $allowedDir)) {
        abort(404);
    }

    return response()->download($realPath);
}
```

## Rate Limiting on Public Routes (Project-Specific)

ALL routes under `open/*` and any unauthenticated public routes MUST have `throttle` middleware to prevent DoS.

Incorrect:
```php
// routes/open.php
Route::get('/open/addresses', [AddressController::class, 'index']);  // No rate limit
```

Correct:
```php
// routes/open.php
Route::get('/open/addresses', [AddressController::class, 'index'])
    ->middleware('throttle:60,1');
```

## Password Hash Consistency (Project-Specific)

When a Model has `'password' => 'hashed'` cast, NEVER use `Hash::make()`, `bcrypt()`, or `password_hash()` in seeders, factories, tests, or controllers. The cast auto-hashes on write — using both causes double-hashing and login failure.

Incorrect:
```php
// Seeder
Admin::create(['password' => Hash::make('password')]);  // Double-hashed!

// Factory
public function definition(): array
{
    return ['password' => bcrypt('password')];  // Double-hashed!
}
```

Correct:
```php
// Seeder
Admin::create(['password' => 'password']);  // Cast auto-hashes

// Factory
public function definition(): array
{
    return ['password' => 'password'];  // Cast auto-hashes
}
```

## Export Token Binding (Project-Specific)

Synchronous export cache tokens MUST be prefixed with the current user's ID to prevent token guessing between users.

Incorrect:
```php
$token = Str::random(32);
Cache::set("export:{$token}", $data);  // Any user could guess this token
```

Correct:
```php
$token = Str::random(32);
Cache::set("export:{$authId}:{$token}", $data);  // Bound to authenticated user
```
