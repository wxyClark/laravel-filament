# Database Performance Best Practices

## Always Eager Load Relationships

Lazy loading causes N+1 query problems — one query per loop iteration. Always use `with()` to load relationships upfront.

Incorrect (N+1 — executes 1 + N queries):
```php
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;
}
```

Correct (2 queries total):
```php
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name;
}
```

Constrain eager loads to select only needed columns (always include the foreign key):

```php
$users = User::with(['posts' => function ($query) {
    $query->select('id', 'user_id', 'title')
          ->where('published', true)
          ->latest()
          ->limit(10);
}])->get();
```

## Prevent Lazy Loading in Development

Enable this in `AppServiceProvider::boot()` to catch N+1 issues during development.

```php
public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
}
```

Throws `LazyLoadingViolationException` when a relationship is accessed without being eager-loaded.

## Select Only Needed Columns

Avoid `SELECT *` — especially when tables have large text or JSON columns.

Incorrect:
```php
$posts = Post::with('author')->get();
```

Correct:
```php
$posts = Post::select('id', 'title', 'user_id', 'created_at')
    ->with(['author:id,name,avatar'])
    ->get();
```

When selecting columns on eager-loaded relationships, always include the foreign key column or the relationship won't match.

## Chunk Large Datasets

Never load thousands of records at once. Use chunking for batch processing.

Incorrect:
```php
$users = User::all();
foreach ($users as $user) {
    $user->notify(new WeeklyDigest);
}
```

Correct:
```php
User::where('subscribed', true)->chunk(200, function ($users) {
    foreach ($users as $user) {
        $user->notify(new WeeklyDigest);
    }
});
```

Use `chunkById()` when modifying records during iteration — standard `chunk()` uses OFFSET which shifts when rows change:

```php
User::where('active', false)->chunkById(200, function ($users) {
    $users->each->delete();
});
```

## Add Database Indexes

Index columns that appear in `WHERE`, `ORDER BY`, `JOIN`, and `GROUP BY` clauses.

Incorrect:
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('status');
    $table->timestamps();
});
```

Correct:
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->index()->constrained();
    $table->string('status')->index();
    $table->timestamps();
    $table->index(['status', 'created_at']);
});
```

Add composite indexes for common query patterns (e.g., `WHERE status = ? ORDER BY created_at`).

## Use `withCount()` for Counting Relations

Never load entire collections just to count them.

Incorrect:
```php
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->comments->count();
}
```

Correct:
```php
$posts = Post::withCount('comments')->get();
foreach ($posts as $post) {
    echo $post->comments_count;
}
```

Conditional counting:

```php
$posts = Post::withCount([
    'comments',
    'comments as approved_comments_count' => function ($query) {
        $query->where('approved', true);
    },
])->get();
```

## Use `cursor()` for Memory-Efficient Iteration

For read-only iteration over large result sets, `cursor()` loads one record at a time via a PHP generator.

Incorrect:
```php
$users = User::where('active', true)->get();
```

Correct:
```php
foreach (User::where('active', true)->cursor() as $user) {
    ProcessUser::dispatch($user->id);
}
```

Use `cursor()` for read-only iteration. Use `chunk()` / `chunkById()` when modifying records.

## No Queries in Blade Templates

Never execute queries in Blade templates. Pass data from controllers.

Incorrect:
```blade
@foreach (User::all() as $user)
    {{ $user->profile->name }}
@endforeach
```

Correct:
```php
// Controller
$users = User::with('profile')->get();
return view('users.index', compact('users'));
```

```blade
@foreach ($users as $user)
    {{ $user->profile->name }}
@endforeach
```

## No Recursive DB Queries for Tree Structures (Project-Specific)

Tree data (categories, addresses, org charts) MUST use batch loading + in-memory construction. Never use `while ($node->parent)` loops or unbounded recursive DB calls.

Incorrect (N+1 recursive — fires one query per node):
```php
$current = $node;
while ($current->parent) {       // Each iteration = 1 SELECT
    $current = $current->parent;  // For 1000 nodes = 1000 queries
}
```

Correct (batch load + memory):
```php
// Load all parents in one query
$parentIds = collect(explode(',', $node->merge_path))->filter()->values();
$parents = Address::whereIn('id', $parentIds)->keyBy('id');

// Build chain from memory
$chain = [];
$current = $node;
while ($current) {
    $chain[] = $current;
    $current = $current->parent_id ? $parents[$current->parent_id] ?? null : null;
}
```

For counting descendants, use a recursive CTE instead of recursive PHP:

```php
// ✅ Single query with CTE
$descendantCount = DB::select("
    WITH RECURSIVE descendants AS (
        SELECT id FROM addresses WHERE parent_id = ?
        UNION ALL
        SELECT a.id FROM addresses a
        INNER JOIN descendants d ON a.parent_id = d.id
    )
    SELECT COUNT(*) as count FROM descendants
", [$node->id])[0]->count;
```

## Batch Inserts for Large Imports (Project-Specific)

NEVER call `Model::create()` in a loop for bulk data. Use `chunk()` + `DB::table()->insert()` or `Model::insert()`.

Incorrect (80,000 records = 80,000 queries):
```php
foreach ($records as $record) {
    Address::create($record);  // One INSERT per iteration
}
```

Correct (80,000 records = 80 queries):
```php
collect($records)->chunk(1000)->each(function ($chunk) {
    DB::table('addresses')->insert($chunk->toArray());
});
```

Also avoid loading all records into memory for deletion:
```php
// ❌ Loads everything into memory
Address::query()->withTrashed()->get()->each->forceDelete();

// ✅ Use chunked deletion
Address::query()->withTrashed()->chunkById(1000, function ($addresses) {
    $addresses->each->forceDelete();
});
```

## Cache Filament Form/Table Options (Project-Specific)

Every Filament page render triggers form/table option queries. These MUST be cached.

Incorrect:
```php
Select::make('parent_id')
    ->options(Address::whereNull('parent_id')->pluck('name', 'id'))  // DB hit per render
```

Correct:
```php
Select::make('parent_id')
    ->options(fn () => Cache::remember('address:root_options', 3600, fn () =>
        Address::whereNull('parent_id')->orderBy('sort')->pluck('name', 'id')
    ))
```
