# Testing Best Practices

## Use `LazilyRefreshDatabase` Over `RefreshDatabase`

`RefreshDatabase` migrates once per process and wraps each test in a rolled-back transaction. `LazilyRefreshDatabase` skips even that first migration if the schema is already up to date.

## Use Model Assertions Over Raw Database Assertions

Incorrect: `$this->assertDatabaseHas('users', ['id' => $user->id]);`

Correct: `$this->assertModelExists($user);`

More expressive, type-safe, and fails with clearer messages.

## Use Factory States and Sequences

Named states make tests self-documenting. Sequences eliminate repetitive setup.

Incorrect: `User::factory()->create(['email_verified_at' => null]);`

Correct: `User::factory()->unverified()->create();`

## Use `Exceptions::fake()` to Assert Exception Reporting

Instead of `withoutExceptionHandling()`, use `Exceptions::fake()` to assert the correct exception was reported while the request completes normally.

## Call `Event::fake()` After Factory Setup

Model factories rely on model events (e.g., `creating` to generate UUIDs). Calling `Event::fake()` before factory calls silences those events, producing broken models.

Incorrect: `Event::fake(); $user = User::factory()->create();`

Correct: `$user = User::factory()->create(); Event::fake();`

## Use `recycle()` to Share Relationship Instances Across Factories

Without `recycle()`, nested factories create separate instances of the same conceptual entity.

```php
Ticket::factory()
    ->recycle(Airline::factory()->create())
    ->create();
```

## Factory Requirements (Project-Specific)

Every domain model MUST have a Factory. Tests must use factories for test data — never manually create records with hardcoded values.

Required factories:
- `AdminFactory` (exists)
- `CustomerFactory` (missing — create in `database/factories/CustomerFactory.php`)
- `AddressFactory` (missing — create in `database/factories/AddressFactory.php`)

Incorrect:
```php
// Manual creation — brittle, duplicates field definitions
$customer = Customer::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password',
]);
```

Correct:
```php
// Factory — flexible, centralized, reusable
$customer = Customer::factory()->create();
$customer = Customer::factory()->verified()->create(['name' => 'Custom Name']);
```

## Minimum Test Coverage (Project-Specific)

Every new feature must meet these minimums:
- Each Service method: minimum 1 unit test
- Each API endpoint: minimum 1 integration test
- Each Filament resource: list + create + edit + delete tests
- Export functionality: sync CSV/Excel + async job tests

Never mark a feature as complete without tests for the export path, error handling, and authorization.
