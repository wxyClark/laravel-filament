---
name: redis-best-practices
description: "Apply this skill when using Redis for caching, sessions, queues, and real-time features in Laravel. Covers cache patterns, session management, rate limiting, and distributed locking."
license: MIT
metadata:
  author: laravel
---

# Redis Best Practices

## Cache Patterns

### Basic Cache Operations
```php
use Illuminate\Support\Facades\Cache;

// Simple get/set
Cache::put('key', 'value', $seconds = 3600);
$value = Cache::get('key');

// Remember pattern (get or compute)
$users = Cache::remember('users', 3600, function () {
    return User::all();
});

// Forget
Cache::forget('key');

// Check existence
Cache::has('key');
```

### Cache Tags
```php
// Store tagged cache
Cache::tags(['users', 'active'])->put('users', $users, 3600);

// Retrieve tagged cache
Cache::tags(['users', 'active'])->get('users');

// Flush by tag
Cache::tags(['users'])->flush();
Cache::tags(['users', 'active'])->flush();
```

### Cache Many Items
```php
$values = Cache::many([
    'key1' => 'value1',
    'key2' => 'value2',
]);

Cache::putMany($values, 3600);
```

## Remember Lock Pattern

### Prevent Cache Stampede
```php
$users = Cache::lock('users', 10)->block(function () {
    return Cache::remember('users', 3600, function () {
        return User::all();
    });
});
```

### With Callback
```php
$value = Cache::rememberLock('expensive:computation', 3600, function () {
    return computeExpensiveValue();
});
```

## Cache Invalidation

### Update-through Cache
```php
class UserService
{
    public function update(User $user, array $data): User
    {
        $user = $this->userRepository->update($user, $data);
        Cache::forget("user:{$user->id}");
        Cache::forget('users');
        return $user;
    }
}
```

### Event-based Cache Busting
```php
// In EventServiceProvider
protected $listen = [
    'App\Events\UserUpdated' => [
        'App\Listeners\ClearUserCache',
    ],
];

class ClearUserCache
{
    public function handle(UserUpdated $event): void
    {
        Cache::forget("user:{$event->user->id}");
    }
}
```

## Session Management

### Configuration
```php
// config/session.php
'driver' => 'redis',
'connection' => 'session',
'lottery' => [2, 100],
```

### Session Helper Usage
```php
// Store
session(['key' => 'value']);

// Retrieve
$value = session('key', 'default');

// Push to array
session()->push('user.teams', 'developers');

// Pull (get and delete)
$value = session()->pull('key');
```

## Rate Limiting

### Route Middleware
```php
// routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per minute
});

Route::middleware('throttle:5,1')->group(function () {
    // 5 requests per minute (strict)
});
```

### Custom Rate Limiter
```php
// In RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

## Distributed Locking

### Cache Lock
```php
$lock = Cache::lock('processing:order:123', 10);

if ($lock->get()) {
    // Process order
    $lock->release();
}
```

### Block with Timeout
```php
$lock = Cache::lock('processing:order:123', 10);

try {
    $lock->block(5);
    // Got lock, process
} catch (LockTimeoutException $e) {
    // Could not acquire lock
} finally {
    optional($lock)->release();
}
```

### Shared Lock (for reading)
```php
$lock = Cache::lock('users', 10);

$lock->shared()->block(5);

try {
    // Read operation
} finally {
    $lock->shared()->release();
}
```

## Queue with Redis

### Configuration
```php
// config/queue.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => 'default',
    'retry_after' => 90,
    'block_for' => 5,
],
```

### Job Implementation
```php
class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 120;

    public function handle(): void
    {
        // Process podcast
    }

    public function failed(Throwable $exception): void
    {
        // Notify admin
    }
}
```

## Real-time Features

### Broadcasting with Redis
```php
// config/broadcasting.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
],
```

### Event Broadcasting
```php
class OrderShipped implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function broadcastOn(): array
    {
        return [new PrivateChannel('orders.' . $this->order->user_id)];
    }
}
```

## Redis Data Structures

### String Operations
```php
Redis::set('key', 'value');
Redis::get('key');
Redis::incr('counter');
Redis::incrbyfloat('price', 1.5);
Redis::expire('key', 3600);
```

### List Operations
```php
Redis::lpush('queue', 'job1');
Redis::rpop('queue');
Redis::lrange('queue', 0, -1);
Redis::llen('queue');
```

### Set Operations
```php
Redis::sadd('tags', 'php', 'laravel');
Redis::smembers('tags');
Redis::sismember('tags', 'laravel');
Redis::srem('tags', 'php');
```

### Hash Operations
```php
Redis::hset('user:1', 'name', 'John');
Redis::hget('user:1', 'name');
Redis::hgetall('user:1');
Redis::hincrby('user:1', 'visits', 1);
```

## Monitoring and Debugging

### Check Connection
```php
try {
    Redis::ping();
} catch (ConnectionException $e) {
    // Handle connection failure
}
```

### Monitor Commands
```bash
redis-cli monitor
```

### Cache Statistics
```php
// In tinker
Cache::getStore()->getPrefix();
```

## Best Practices

1. **Use meaningful keys**: `users:123` not `u123`
2. **Set TTL always**: Never cache without expiration
3. **Use tags for invalidation**: Group related cache entries
4. **Handle failures gracefully**: Cache failures shouldn't break apps
5. **Monitor memory usage**: Set maxmemory-policy appropriately
6. **Use connection pooling**: For high-traffic applications
7. **Separate sessions/queues**: Use different Redis databases