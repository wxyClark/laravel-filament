---
name: mysql-best-practices
description: "Apply this skill when working with MySQL databases in Laravel. Covers query optimization, indexing, schema design, Eloquent patterns, and performance tuning for MySQL."
license: MIT
metadata:
  author: laravel
---

# MySQL Best Practices

## Indexing Strategy

### When to Add Indexes
- Columns used in WHERE clauses
- Columns used in ORDER BY
- Columns used in JOIN conditions
- Columns with high selectivity (unique values)
- Foreign key columns

### Index Types

#### Single Column Index
```php
// Migration
$table->index('email');
// or
$table->unique('email');
```

#### Composite Index
```php
// Migration - order matters!
$table->index(['user_id', 'status']);
// Query will use index for: WHERE user_id = ? AND status = ?
// Query will NOT use index for: WHERE status = ?
```

#### Covering Index
```php
// Include all columns needed for query
$table->index(['user_id', 'status', 'name']);
// Can satisfy query without accessing table rows
```

### Index Naming
```
idx_{table}_{column(s)}
uniq_{table}_{column(s)}
```

## Query Optimization

### Select Only Needed Columns
```php
// Bad
$users = User::all();

// Good
$users = User::select('id', 'name', 'email')->get();
```

### Use EXISTS over COUNT
```php
// Bad
if (User::where('status', 'active')->count() > 0) {}

// Good
if (User::where('status', 'active')->exists()) {}
```

### Batch Operations
```php
// Insert many records efficiently
User::insert([
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com'],
    // ...
]);
```

### Use Chunking for Large Datasets
```php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});

// Or cursor for memory efficiency
foreach (User::cursor() as $user) {
    // Process user
}
```

## Schema Design

### Use Appropriate Data Types
```php
// Use unsignedBigInteger for IDs
$table->unsignedBigInteger('user_id');

// Use string for short text
$table->string('name', 255);

// Use text for long text
$table->text('description');

// Use enum for fixed options
$table->enum('status', ['pending', 'active', 'completed']);

// Use JSON for flexible data
$table->json('metadata');
```

### Always Define Timestamps
```php
$table->timestamps();
$table->softDeletes();
```

### Migration Best Practices
```php
// Always use safe migration patterns
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->decimal('total', 10, 2);
        $table->enum('status', ['pending', 'paid', 'shipped', 'delivered']);
        $table->timestamps();

        // Index for foreign key
        $table->index('user_id');

        // Composite index for common queries
        $table->index(['user_id', 'status']);
    });
}
```

## Eloquent Optimization

### Eager Loading (Prevent N+1)
```php
// Bad - N+1 problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;
}

// Good - eager load
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name;
}

// Nested eager loading
$posts = Post::with(['author', 'comments.user'])->get();
```

### Lazy Loading Prevention
```php
// In AppServiceProvider boot()
Model::preventLazyLoading(! app()->isProduction());
```

### Use withCount for Counting Relations
```php
// Bad - loads all posts
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->count();
}

// Good - uses COUNT query
$users = User::withCount('posts')->get();
foreach ($users as $user) {
    echo $user->posts_count;
}
```

### Subqueries
```php
// Add subquery for sorting
$orders = Order::select([
    'orders.*',
    'sub.total_amount'
])
->leftJoinSub(
    OrderItem::select('order_id', DB::raw('SUM(price * quantity) as total_amount'))
        ->groupBy('order_id'),
    'sub',
    'orders.id',
    'sub.order_id'
)
->orderBy('sub.total_amount', 'desc')
->get();
```

## Connection Management

### Read/Write Connections
```php
// config/database.php
'mysql' => [
    'read' => [
        'host' => '192.168.1.1',
    ],
    'write' => [
        'host' => '192.168.1.2',
    ],
    'sticky' => true,
    'driver' => 'mysql',
    'database' => 'forge',
    // ...
],
```

### Connection Pooling
```php
// For long-running CLI scripts
DB::connection()->setReconnector(function ($connection) {
    $connection->disconnect();
    $connection->connect();
});
```

## Performance Monitoring

### Explain Queries
```php
// In tinker or debug mode
DB::enableQueryLog();

$users = User::with('posts')->get();

foreach (DB::getQueryLog() as $query) {
    dump($query['query']);
    dump($query['bindings']);
}
```

### Use Laravel Debugbar
```bash
composer require barryvdh/laravel-debugbar --dev
```

### Database Index Analysis
```sql
-- Find unused indexes
SELECT * FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE object_schema = 'your_database'
AND index_name IS NOT NULL
AND count_star = 0;

-- Find slow queries
SHOW FULL PROCESSLIST;
```

## Common Patterns

### Soft Deletes
```php
// Model
class User extends Model
{
    use SoftDeletes;
}

// Query (automatically excludes soft deleted)
$user = User::find(1);

// Include soft deleted
$user = User::withTrashed()->find(1);

// Query only soft deleted
$users = User::onlyTrashed()->get();
```

### UUIDs
```php
// Migration
$table->uuid('id')->primary();
$table->uuid('user_id');

// Model
class Order extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
}
```