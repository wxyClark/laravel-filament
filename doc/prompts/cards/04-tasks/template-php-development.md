# 任务模板：PHP 开发规范

> **v2.0: 专业知识讲解 + 面试引导**

## 用途说明
规范 PHP 开发过程，帮助开发者理解 PHP 开发的专业知识和最佳实践。

## 适用场景
- 编写 PHP 代码前的规范学习
- 代码评审前的规范检查
- 提升 PHP 开发能力

---

## 📚 专业知识讲解

### 1. PHP 核心概念

**PHP 8.2+ 新特性：**
```php
// 1. 构造函数属性提升
class User {
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}

// 2. 只读类
readonly class UserDTO {
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}

// 3. 枚举类型
enum Status: string {
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
}

// 4. 匹配表达式
$result = match($status) {
    Status::Draft => '草稿',
    Status::Active => '活跃',
    Status::Inactive => '不活跃',
};

// 5. Fibers（协程）
$fiber = new Fiber(function (): void {
    echo "Hello\n";
    Fiber::suspend();
    echo "World\n";
});
$fiber->start();
$fiber->resume();
```

### 2. PHP 设计模式

**依赖注入：**
```php
// ❌ 错误方式
class OrderService {
    public function create() {
        $db = new Database(); // 硬编码依赖
    }
}

// ✅ 正确方式
class OrderService {
    public function __construct(
        private readonly Database $db
    ) {}
    
    public function create() {
        $this->db->query(...);
    }
}
```

**工厂模式：**
```php
interface PaymentInterface {
    public function pay(float $amount): bool;
}

class PaymentFactory {
    public static function create(string $type): PaymentInterface {
        return match($type) {
            'wechat' => new WechatPayment(),
            'alipay' => new AlipayPayment(),
            default => throw new InvalidArgumentException("Unknown payment type: {$type}"),
        };
    }
}
```

**策略模式：**
```php
interface DiscountStrategy {
    public function calculate(float $price): float;
}

class VoucherDiscount implements DiscountStrategy {
    public function calculate(float $price): float {
        return $price * 0.9; // 9折
    }
}

class FullReductionDiscount implements DiscountStrategy {
    public function __construct(
        private float $threshold,
        private float $reduction
    ) {}
    
    public function calculate(float $price): float {
        return $price >= $this->threshold ? $price - $this->reduction : $price;
    }
}
```

### 3. PHP 性能优化

**数据库查询优化：**
```php
// ❌ N+1 查询
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name; // 每次循环都查询用户
}

// ✅ 预加载
$orders = Order::with('user')->get();
foreach ($orders as $order) {
    echo $order->user->name; // 使用预加载的数据
}

// ✅ Chunk 处理大数据
Order::chunk(100, function ($orders) {
    foreach ($orders as $order) {
        // 处理订单
    }
});
```

**缓存优化：**
```php
// ❌ 每次都查询数据库
$user = User::find($id);

// ✅ 使用缓存
$user = Cache::remember("user:{$id}", 3600, function () use ($id) {
    return User::find($id);
});

// ✅ 缓存失效策略
Cache::forget("user:{$id}");
```

### 4. PHP 安全实践

**SQL 注入防护：**
```php
// ❌ 直接拼接 SQL
$sql = "SELECT * FROM users WHERE id = {$id}";

// ✅ 使用参数绑定
$user = DB::select('SELECT * FROM users WHERE id = ?', [$id]);

// ✅ 使用 Eloquent
$user = User::find($id);
```

**XSS 防护：**
```php
// ❌ 直接输出用户输入
echo $userInput;

// ✅ 转义输出
echo e($userInput);

// ✅ 使用 Blade 模板
{!! nl2br(e($userInput)) !!}
```

**CSRF 防护：**
```php
// 在表单中添加 CSRF token
<form method="POST" action="/submit">
    @csrf
    <!-- 表单内容 -->
</form>

// 在路由中验证 CSRF
Route::post('/submit', [Controller::class, 'submit'])
    ->middleware('csrf');
```

---

## 🎯 面试引导流程

### 第一轮：理解需求

```
1. 这个功能的业务逻辑是什么？
   - 输入是什么？
   - 输出是什么？
   - 处理流程是什么？

2. 数据模型是什么？
   - 有哪些实体？
   - 实体间的关系？

3. 性能要求是什么？
   - 响应时间要求？
   - 并发量要求？
```

### 第二轮：设计方案

```
4. 代码结构如何设计？
   - 使用什么设计模式？
   - 如何组织代码？

5. 数据库如何交互？
   - 使用 Eloquent 还是 Query Builder？
   - 如何优化查询？

6. 异常如何处理？
   - 有哪些异常情况？
   - 如何优雅处理？
```

### 第三轮：实现优化

```
7. 代码质量如何保证？
   - 类型声明？
   - 代码规范？
   - 单元测试？

8. 性能如何优化？
   - 缓存策略？
   - 查询优化？

9. 安全如何保障？
   - 输入验证？
   - 输出转义？
   - 权限控制？
```

---

## 📝 标准输出格式

```markdown
# PHP 开发任务

## 📋 需求理解
{用自己的话描述需求}

## 🏗️ 架构设计
{代码结构和设计模式}

## 💾 数据模型
{实体和关系设计}

## 🔧 核心代码

### {类名/方法名}
```php
<?php
declare(strict_types=1);

// 代码实现
```

### 代码解释
{解释关键设计决策}

## 🧪 单元测试
```php
// 测试代码
```

## 📊 性能优化
{性能优化措施}

## 🛡️ 安全措施
{安全防护措施}
```

---

## 💡 知识点总结

### PHP 开发的核心原则

1. **类型安全**：使用严格类型声明
2. **依赖注入**：通过构造函数注入依赖
3. **单一职责**：每个类只做一件事
4. **开闭原则**：对扩展开放，对修改关闭
5. **防御性编程**：假设输入不可信

### PHP 开发的检查清单

- [ ] 是否声明了 `strict_types`？
- [ ] 是否使用了类型声明？
- [ ] 是否使用了依赖注入？
- [ ] 是否处理了异常？
- [ ] 是否进行了输入验证？
- [ ] 是否进行了输出转义？
- [ ] 是否有单元测试？

---

**版本**: v2.0 | **更新日期**: 2026-04-27
