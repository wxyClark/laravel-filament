---
name: laravel-architecture
description: "Apply this skill for Laravel architecture patterns including thin controllers, service classes, repository pattern, model definitions, constants, and enterprise-level code organization."
license: MIT
metadata:
  author: laravel
---

# Laravel Architecture Patterns

## Thin Controller Principle

Controllers should be thin - they only handle:
- HTTP request/response
- Input validation (via FormRequest)
- Calling services
- Returning responses

### Controller Example (Good)
```php
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());
        return response()->json(['data' => $user], 201);
    }
}
```

### Controller Example (Bad)
```php
class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Too much logic here!
        $validated = $request->validate([...]);
        $user = User::create($validated);
        // More logic...
        Mail::to($user)->send(new WelcomeMail());
        return response()->json(['data' => $user], 201);
    }
}
```

## Service Layer

Services handle business logic and should:
- Be injected via constructor
- Have single responsibility
- Be named descriptively (e.g., `UserService`, `OrderService`)
- Return DTOs or entities, not arrays

### Service Example
```php
namespace App\Services;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MailService $mailService
    ) {}

    public function create(array $data): User
    {
        $user = $this->userRepository->create($data);
        $this->mailService->sendWelcomeEmail($user);
        return $user;
    }
}
```

## Repository Pattern

Repositories abstract data access:

### Repository Interface
```php
namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
}
```

### Repository Implementation
```php
namespace App\Repositories;

class UserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
```

## Model Definition

Models should define:
- Table name (if not following convention)
- Fillable columns
- Hidden columns
- Casts
- Relationships
- Scopes

### Model Example
```php
namespace App\Models;

class User extends Model
{
    protected $table = 'users';

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

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
```

## Constants Definition

Use constants for magic numbers and strings:

### In Models
```php
class Order extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'status',
        // ...
    ];
}
```

### As Enum (PHP 8.1+)
```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
```

## Form Request Validation

Use dedicated Form Request classes:

```php
namespace App\Http\Requests;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }
}
```

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── UserController.php
│   ├── Requests/
│   │   └── User/
│   │       ├── StoreUserRequest.php
│   │       └── UpdateUserRequest.php
│   └── Resources/
│       └── UserResource.php
├── Models/
│   └── User.php
├── Repositories/
│   ├── Contracts/
│   │   └── UserRepositoryInterface.php
│   └── Eloquent/
│       └── UserRepository.php
├── Services/
│   └── UserService.php
└── Enums/
    └── UserRole.php
```