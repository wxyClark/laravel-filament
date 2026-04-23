---
name: queue-jobs-best-practices
description: "Apply this skill when implementing queue jobs and message processing in Laravel. Covers job configuration, retry strategies, failed job handling, and queue patterns for RabbitMQ and other queue drivers."
license: MIT
metadata:
  author: laravel
---

# Queue Jobs Best Practices

## Job Structure

### Basic Job Class
```php
namespace App\Jobs;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public array $backoff = [10, 60, 300];
    public int $maxExceptions = 3;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        // Process the order
        $this->order->markAsProcessed();
    }

    public function failed(Throwable $exception): void
    {
        $this->order->markAsFailed();
        Notification::route('mail', 'admin@example.com')
            ->notify(new OrderProcessingFailed($this->order));
    }
}
```

### Job Middleware
```php
class RateLimitedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function middleware(): array
    {
        return [new RateLimited];
    }

    public function handle(): void
    {
        // Job logic
    }
}
```

## Queue Configuration

### config/queue.php
```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 5,
        'after_commit' => false,
    ],
],

'failed' => [
    'driver' => 'redis',
    'database' => 'default',
    'table' => 'failed_jobs',
],
```

### Environment Configuration
```env
QUEUE_CONNECTION=redis
REDIS_QUEUE=default
```

## Dispatching Jobs

### Basic Dispatch
```php
ProcessOrder::dispatch($order);
ProcessOrder::dispatchSync($order); // Immediate
ProcessOrder::dispatchNow($order); // Synchronous
```

### Delayed Dispatch
```php
ProcessOrder::dispatch($order)
    ->delay(now()->addMinutes(5));
```

### Chaining
```php
ProcessOrder::withChain([
    new SendOrderConfirmation($order),
    new NotifyAdmin($order),
])->dispatch($order);
```

### On Queue
```php
ProcessOrder::dispatch($order)->onQueue('high');
ProcessOrder::dispatch($order)->onQueue('low');
```

### Conditional Dispatch
```php
ProcessOrder::dispatchIf($shouldProcess, $order);
ProcessOrder::dispatchUnless($shouldNotProcess, $order);
```

## Retry Configuration

### Per-Job Configuration
```php
class ProcessOrder implements ShouldQueue
{
    public int $tries = 5;
    public int $timeout = 300;
    public array $backoff = [30, 60, 120, 240, 480]; // Exponential backoff
    public int $maxExceptions = 3;

    public function backoff(): array
    {
        return [30, 60, 120];
    }
}
```

### Global Configuration
```php
// config/queue.php
'failed' => [
    'driver' => 'database-uuids',
    'database' => 'mysql',
    'table' => 'failed_jobs',
],
```

### Retry Until
```php
class ProcessOrder implements ShouldQueue
{
    public int $tries = 0; // Unlimited

    public function retryUntil(): DateTime
    {
        return now()->addHours(1);
    }
}
```

## Unique Jobs

### Prevent Duplicate Jobs
```php
class ProcessOrder implements ShouldQueue
{
    use WithoutDuplicates;

    public int $tries = 3;
    public int $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return $this->order->id;
    }
}
```

### Should Be Unique
```php
class ProcessOrder implements ShouldQueue, ShouldBeUnique
{
    public int $tries = 3;

    public function uniqueId(): mixed
    {
        return $this->order->id;
    }

    public function uniqueFor(): int
    {
        return 3600;
    }
}
```

## Database Transactions

### Handle After Commit
```php
class ProcessOrder implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(): void
    {
        // Safe to assume order exists in DB
    }
}
```

### Within Transaction
```php
DB::transaction(function () use ($order) {
    $order->status = 'processing';
    $order->save();

    ProcessOrder::dispatch($order);
}); // Job will be dispatched after transaction commits
```

## Monitoring with Horizon

### Installation
```bash
composer require laravel/horizon
php artisan horizon:install
```

### Configuration
```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

## Failed Jobs

### Manual Retry
```bash
php artisan queue:retry all
php artisan queue:retry 5d734e9c-6e2e-4b0e-9f0e-5c8d7b3a2f1e
```

### Clear Failed Jobs
```bash
php artisan queue:flush
php artisan queue:forget 5d734e9c-6e2e-4b0e-9f0e-5c8d7b3a2f1e
```

### Failed Job Model
```php
class FailedJob extends Model
{
    protected $table = 'failed_jobs';

    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
    ];
}
```

## Job Batching

### Create Batch
```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$batch = Bus::batch([
    new ProcessOrders($orders->where('status', 'pending')),
    new SendOrderNotifications($orders),
    new UpdateInventory($orders),
])->then(function (Batch $batch) {
    // All jobs completed successfully
})->catch(function (Batch $batch, Throwable $e) {
    // First batch job failure detected
})->finally(function (Batch $batch) {
    // Batch execution completed
})->dispatch();
```

### Check Batch Progress
```php
if ($batch->progress() === 100) {
    // Batch completed
}

$batch->failedJobs; // Number of failed jobs
$batch->succeededJobs; // Number of succeeded jobs
```

## Rate Limiting Queues

### Rate Limited Jobs
```php
class SendWelcomeEmail implements ShouldQueue
{
    public int $tries = 3;

    public function handle(): void
    {
        // Send email with rate limiting
    }
}

// Route middleware
Route::middleware('throttle:60,1')->group(function () {
    // ...
});
```

## Monitoring

### Queue Stats
```bash
php artisan queue:monitor redis:default,redis:high
```

### Failed Jobs Table
```bash
php artisan queue:failed-table
php artisan migrate
```

## Best Practices

1. **Always set timeout**: Prevent zombie jobs
2. **Use exponential backoff**: Give external services time to recover
3. **Implement failed() method**: Log and notify on failures
4. **Use unique jobs**: Prevent duplicate processing
5. **Handle transactions properly**: Use `$afterCommit = true`
6. **Use job middleware**: For rate limiting, throttling
7. **Monitor failed jobs**: Set up alerts
8. **Use Horizon for Redis**: Get visibility into queue processing
9. **Keep jobs idempotent**: Same input = same output
10. **Don't queue heavy views**: Process data, not rendering