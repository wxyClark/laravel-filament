---
name: restful-api-routing
description: "Apply this skill when designing and implementing RESTful API routes in Laravel. Covers resource routing, API versioning, JSON responses, and best practices for REST API design."
license: MIT
metadata:
  author: laravel
---

# RESTful API Routing Best Practices

## Standard REST Endpoints

| Method | Endpoint | Action | Description |
|--------|----------|--------|-------------|
| GET | /users | index | List all users |
| GET | /users/{id} | show | Get single user |
| POST | /users | store | Create new user |
| PUT | /users/{id} | update | Update user (full) |
| PATCH | /users/{id} | update | Update user (partial) |
| DELETE | /users/{id} | destroy | Delete user |

## Route Definition

### API Routes File
```php
// routes/api.php
use App\Http\Controllers\Api\UserController;

Route::prefix('v1')->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### Resource Controller Methods
```php
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        // GET /api/v1/users
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        // POST /api/v1/users
    }

    public function show(User $user): JsonResponse
    {
        // GET /api/v1/users/{user}
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // PUT/PATCH /api/v1/users/{user}
    }

    public function destroy(User $user): JsonResponse
    {
        // DELETE /api/v1/users/{user}
    }
}
```

## Nested Resources

### One Level Deep
```php
Route::apiResource('posts.comments', CommentController::class);
// /posts/{post}/comments
// /posts/{post}/comments/{comment}
```

### Multiple Levels (Use Scoped Binding)
```php
Route::apiResource('posts.comments.replies', ReplyController::class)->scoped();
// /posts/{post}/comments/{comment}/replies
// /posts/{post}/comments/{comment}/replies/{reply}
```

## API Versioning

### Version Prefix
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // v1 routes
});

Route::prefix('v2')->group(function () {
    // v2 routes
});
```

### Deprecated Routes
```php
Route::prefix('v1')->group(function () {
    Route::get('users', [UserController::class, 'index'])
        ->name('api.v1.users.index');
});

Route::middleware('deprecated')->group(function () {
    // Deprecated routes
});
```

## Response Format

### Success Response
```php
// Single resource
return response()->json([
    'data' => new UserResource($user)
], 200);

// Collection
return response()->json([
    'data' => UserResource::collection($users),
    'meta' => [
        'total' => $users->total(),
        'per_page' => $users->perPage(),
    ]
], 200);

// Created
return response()->json([
    'data' => new UserResource($user)
], 201);
```

### Error Response
```php
return response()->json([
    'message' => 'Validation failed',
    'errors' => [
        'email' => ['The email field is required.']
    ]
], 422);

return response()->json([
    'message' => 'Resource not found'
], 404);
```

## Resource Classes

### API Resource
```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

### Collection
```php
class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
            ]
        ];
    }
}
```

## Route Protection

### Authentication
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### Rate Limiting
```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### Policy Authorization
```php
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return response()->json(['data' => $user]);
    }
}
```

## Query Parameters

### Filtering
```php
public function index(Request $request): JsonResponse
{
    $query = User::query();

    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    if ($request->has('search')) {
        $query->where('name', 'LIKE', '%' . $request->search . '%');
    }

    $users = $query->paginate(15);

    return UserResource::collection($users);
}
```

### Sorting
```php
public function index(Request $request): JsonResponse
{
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');

    $users = User::orderBy($sortBy, $sortOrder)->paginate(15);

    return UserResource::collection($users);
}
```

### Including Relations
```php
public function index(Request $request): JsonResponse
{
    $users = User::with($request->get('include', []))->paginate(15);

    return UserResource::collection($users);
}
```