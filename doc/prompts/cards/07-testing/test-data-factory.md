# 测试模板：数据工厂 (Model Factories)

## 用途说明
使用 Factory 快速生成测试数据，保持测试数据的一致性和可维护性。

## 适用场景
- 单元测试数据准备
- 功能测试数据准备
- 性能测试数据生成

## 标准内容块
```markdown
## Model Factory 模板

### 基础 Factory
```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * 定义模型的默认状态
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(10),
            'slug' => fake()->unique()->slug(),
            'content' => fake()->paragraphs(3, true),
            'excerpt' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'draft', 'published']),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'view_count' => fake()->numberBetween(0, 10000),
        ];
    }

    /**
     * 草稿状态
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * 已发布状态
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * 热门文章（高浏览量）
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => fake()->numberBetween(1000, 100000),
            'status' => 'published',
        ]);
    }

    /**
     * 指定作者
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 指定分类
     */
    public function inCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
```

### 复杂 Factory（带关联）
```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_sn' => fn () => 'ORD-' . fake()->unique()->numerify('########'),
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(['pending', 'pending', 'paid']),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'discount_amount' => 0,
            'pay_amount' => fn (array $attributes) => $attributes['total_amount'],
            'shipping_info' => [
                'name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'province' => fake()->province(),
                'city' => fake()->city(),
                'address' => fake()->address(),
            ],
            'notes' => fake()->optional(0.3)->sentence(),
            'paid_at' => null,
            'shipped_at' => null,
            'completed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'paid_at' => fake()->dateTimeBetween('-14 days', '-7 days'),
            'shipped_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_at' => fake()->dateTimeBetween('-30 days', '-14 days'),
            'shipped_at' => fake()->dateTimeBetween('-14 days', '-7 days'),
            'completed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function withItems(int $count = 3): static
    {
        return $this->has(
            OrderItem::factory()->count($count),
            'items'
        );
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }
}
```

### 使用方式
```php
// 创建单个
$post = Post::factory()->create();

// 批量创建
$posts = Post::factory()->count(10)->create();

// 使用状态
$draftPost = Post::factory()->draft()->create();
$publishedPost = Post::factory()->published()->create();
$popularPost = Post::factory()->popular()->create();

// 覆盖属性
$post = Post::factory()->create([
    'title' => 'Custom Title',
    'user_id' => $user->id,
]);

// 使用状态 + 覆盖属性
$post = Post::factory()->published()
    ->byUser($user)
    ->inCategory($category)
    ->create(['title' => 'Specific Post']);

// 关联创建
$user = User::factory()
    ->has(Post::factory()->count(3))
    ->create();

// 创建订单带明细
$order = Order::factory()
    ->paid()
    ->withItems(5)
    ->forCustomer($customer)
    ->create();

// 建造模式（不保存）
$post = Post::factory()->make();
$post = Post::factory()->make(['title' => 'Test']);

// 序列化
$users = User::factory()->sequence(
    fn ($sequence) => ['name' => "User {$sequence->index}"],
)->count(3)->create();
```

### 数据库清理策略
```php
// phpunit.xml 或 pest.php
<phpunit>
    <extensions>
        <extension class="Illuminate\Foundation\Testing\DatabaseTruncation"/>
    </extensions>
</phpunit>

// 或者在 Pest 中配置
uses(Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');
```

### Factory 最佳实践
1. **使用 faker**: 生成随机但有意义的数据
2. **定义状态**: 为常见场景定义状态方法
3. **链式调用**: 支持 `->state()->association()->create()`
4. **避免硬编码**: 使用 `fn ()` 延迟计算关联值
5. **保持简单**: Factory 只负责创建数据，不包含业务逻辑
```
```
