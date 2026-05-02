# 📋 API 接口测试文档

> **测试接口清单** | **使用方法** | **注意事项**

---

## 📁 目录结构

```
tests/
├── Pest.php                              # Pest 配置
├── TestCase.php                          # 基础测试类
├── README.md                             # 本文档
│
├── Api/                                  # API 测试根目录
│   ├── Traits/                           # 共享 Traits
│   │   ├── ResponseAssert.php            # 响应断言
│   │   ├── JsonResponseHelper.php        # JSON 解析（支持中文）
│   │   ├── ParameterDriven.php           # 参数化驱动
│   │   └── DatabaseHelper.php            # 数据库辅助
│   │
│   ├── V1/                               # API 版本
│   │   ├── Auth/                         # 认证模块
│   │   ├── Ecommerce/                    # 电商模块
│   │   ├── O2O/                          # O2O 预约模块
│   │   ├── Distribution/                 # 分销模块
│   │   ├── RBAC/                         # 权限模块
│   │   ├── CRM/                          # 客户模块
│   │   ├── DRP/                          # 进销存模块
│   │   └── Finance/                      # 财务模块
│   │
│   └── Scenarios/                        # 场景测试
│
└── Fixtures/                             # 测试数据配置
    └── parameters.yaml                   # 参数化配置文件
```

---

## 📊 测试接口清单

### Auth 认证模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 登录 | POST | /api/v1/auth/login | Auth/LoginTest.php | 用户登录 |
| 注册 | POST | /api/v1/auth/register | Auth/RegisterTest.php | 用户注册 |
| 退出 | POST | /api/v1/auth/logout | Auth/LogoutTest.php | 用户退出 |
| 用户信息 | GET | /api/v1/auth/user | Auth/UserTest.php | 获取当前用户 |

### Ecommerce 电商模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 商品列表 | GET | /api/v1/products | Ecommerce/ProductTest.php | 商品列表 |
| 商品详情 | GET | /api/v1/products/{id} | Ecommerce/ProductTest.php | 商品详情 |
| 创建商品 | POST | /api/v1/products | Ecommerce/ProductTest.php | 创建商品 |
| 更新商品 | PUT | /api/v1/products/{id} | Ecommerce/ProductTest.php | 更新商品 |
| 删除商品 | DELETE | /api/v1/products/{id} | Ecommerce/ProductTest.php | 删除商品 |
| 分类列表 | GET | /api/v1/categories | Ecommerce/CategoryTest.php | 分类列表 |
| 购物车列表 | GET | /api/v1/cart | Ecommerce/CartTest.php | 购物车列表 |
| 添加购物车 | POST | /api/v1/cart | Ecommerce/CartTest.php | 添加购物车 |
| 更新购物车 | PUT | /api/v1/cart/{id} | Ecommerce/CartTest.php | 更新购物车 |
| 删除购物车 | DELETE | /api/v1/cart/{id} | Ecommerce/CartTest.php | 删除购物车 |
| 创建订单 | POST | /api/v1/orders | Ecommerce/OrderTest.php | 创建订单 |
| 订单列表 | GET | /api/v1/orders | Ecommerce/OrderTest.php | 订单列表 |
| 取消订单 | POST | /api/v1/orders/{id}/cancel | Ecommerce/OrderTest.php | 取消订单 |
| 确认收货 | POST | /api/v1/orders/{id}/confirm | Ecommerce/OrderTest.php | 确认收货 |
| 支付订单 | POST | /api/v1/orders/{id}/pay | Ecommerce/PaymentTest.php | 支付订单 |

### O2O 预约模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 服务列表 | GET | /api/v1/services | O2O/ServiceTest.php | 服务列表 |
| 创建预约 | POST | /api/v1/bookings | O2O/BookingTest.php | 创建预约 |
| 预约列表 | GET | /api/v1/bookings | O2O/BookingTest.php | 预约列表 |
| 取消预约 | POST | /api/v1/bookings/{id}/cancel | O2O/BookingTest.php | 取消预约 |
| 门店列表 | GET | /api/v1/stores | O2O/StoreTest.php | 门店列表 |
| 门店详情 | GET | /api/v1/stores/{id} | O2O/StoreTest.php | 门店详情 |

### Distribution 分销模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 分销员列表 | GET | /api/v1/distributors | Distribution/DistributorTest.php | 分销员列表 |
| 申请成为分销员 | POST | /api/v1/distributors/apply | Distribution/DistributorTest.php | 申请分销 |
| 佣金列表 | GET | /api/v1/commissions | Distribution/CommissionTest.php | 佣金列表 |
| 申请提现 | POST | /api/v1/withdrawals | Distribution/CommissionTest.php | 申请提现 |

### RBAC 权限模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 角色列表 | GET | /api/v1/roles | RBAC/RoleTest.php | 角色列表 |
| 创建角色 | POST | /api/v1/roles | RBAC/RoleTest.php | 创建角色 |
| 权限列表 | GET | /api/v1/permissions | RBAC/PermissionTest.php | 权限列表 |

### CRM 客户模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 客户列表 | GET | /api/v1/customers | CRM/CustomerTest.php | 客户列表 |
| 创建客户 | POST | /api/v1/customers | CRM/CustomerTest.php | 创建客户 |
| 机会列表 | GET | /api/v1/opportunities | CRM/OpportunityTest.php | 机会列表 |

### DRP 进销存模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 库存列表 | GET | /api/v1/inventory | DRP/InventoryTest.php | 库存列表 |
| 库存调整 | POST | /api/v1/inventory/{id}/adjust | DRP/InventoryTest.php | 库存调整 |
| 采购单列表 | GET | /api/v1/purchase-orders | DRP/PurchaseTest.php | 采购单列表 |

### Finance 财务模块

| 接口 | 方法 | 路径 | 测试文件 | 说明 |
|------|------|------|---------|------|
| 付款单列表 | GET | /api/v1/payment-orders | Finance/PaymentOrderTest.php | 付款单列表 |
| 创建付款单 | POST | /api/v1/payment-orders | Finance/PaymentOrderTest.php | 创建付款单 |
| 发票列表 | GET | /api/v1/invoices | Finance/InvoiceTest.php | 发票列表 |

---

## 🚀 使用方法

### 1. 快速测试单个接口

```bash
# 测试商品列表接口
php artisan test --filter=test_product_list_success

# 测试商品详情接口（使用参数）
TEST_PRODUCT_ID=100 php artisan test --filter=test_product_detail_success
```

### 2. 测试整个模块

```bash
# 测试电商模块所有接口
php artisan test tests/Api/V1/Ecommerce/

# 测试认证模块所有接口
php artisan test tests/Api/V1/Auth/
```

### 3. 运行场景测试

```bash
# 运行订单全流程测试
php artisan test tests/Api/Scenarios/OrderFlowTest.php
```

### 4. 使用参数化测试

```bash
# 设置测试参数
export TEST_PRODUCT_ID=100
export TEST_CATEGORY_ID=5
export TEST_ORDER_ID=200

# 运行测试
php artisan test tests/Api/V1/Ecommerce/
```

### 5. 并行测试

```bash
# 并行运行所有测试
php artisan test --parallel
```

---

## ⚙️ 参数化配置

### 参数文件位置

```
tests/Fixtures/parameters.yaml
```

### 参数文件示例

```yaml
# tests/Fixtures/parameters.yaml

# 默认参数
defaults:
  category_id: 1
  product_id: 1
  user_id: 1

# 场景参数
scenarios:
  # 正常场景
  normal:
    category_id: 1
    product_id: 10
    user_id: 100
    
  # 边界场景
  boundary:
    category_id: 999999
    product_id: 999999
    user_id: 999999
    
  # 性能场景
  performance:
    category_id: 1
    product_id: 1
    user_id: 1
    per_page: 100
```

### 环境变量覆盖

```bash
# 使用环境变量覆盖参数文件中的值
TEST_PRODUCT_ID=200 php artisan test --filter=test_product_detail
```

---

## 🔧 自定义 Traits

### ResponseAssert - 响应断言

```php
// 使用示例
$this->assertSuccessResponse($response);
$this->assertCreatedResponse($response);
$this->assertErrorResponse($response, 404, '商品不存在');
$this->assertValidationErrors($response, ['name', 'price']);
$this->assertPaginatedResponse($response, ['id', 'name', 'price']);
```

### JsonResponseHelper - JSON 解析

```php
// 使用示例
$message = $this->getChineseMessage($response);
$errorDisplay = $this->formatErrorForDisplay($response);
$data = $this->getResponseData($response);
$meta = $this->getResponseMeta($response);
```

### ParameterDriven - 参数化驱动

```php
// 使用示例
$categoryId = $this->getTestId('category');
$params = $this->getScenarioParameters('parameters.yaml', 'normal');
$names = $this->getScenarioNames('parameters.yaml');
```

### DatabaseHelper - 数据库辅助

```php
// 使用示例
$count = $this->getTableCount('products');
$exists = $this->recordExists('products', ['id' => 1]);
$record = $this->getRecord('products', ['id' => 1]);
$this->assertTableStructureUnchanged('products', $columns);
```

---

## 🛡️ 数据安全

### 核心原则

1. **不修改表结构**：测试不能改变数据库表结构
2. **不清空数据**：不能使用 RefreshDatabase
3. **事务隔离**：每个测试在独立事务中运行
4. **测试数据标记**：测试数据添加 is_test 标记

### 事务隔离实现

```php
// TestCase.php
protected function setUp(): void
{
    parent::setUp();
    $this->beginDatabaseTransaction();
}

protected function tearDown(): void
{
    $this->rollbackDatabaseTransactions();
    parent::tearDown();
}
```

### 测试数据 Factory

```php
// 使用 Factory 创建测试数据
$product = Product::factory()->create([
    'is_test' => true,
]);

// 测试结束后，事务回滚，数据自动清理
```

---

## ⚠️ 注意事项

### 1. 测试隔离

- 每个测试用例应该独立运行
- 不要依赖其他测试的执行结果
- 使用 Factory 创建测试数据

### 2. 数据安全

- 不要使用 RefreshDatabase
- 不要直接操作数据库表结构
- 测试数据使用 Factory 创建

### 3. 错误处理

- 使用中文断言信息
- 使用 formatErrorForDisplay() 显示错误
- 记录详细的错误日志

### 4. 性能考虑

- 避免不必要的数据库查询
- 使用 with() 预加载关联数据
- 考虑使用并行测试

### 5. 维护性

- 测试文件按模块组织
- 使用有意义的测试方法名
- 添加详细的注释

---

## 📊 测试报告

### 生成测试报告

```bash
# 生成 HTML 报告
php artisan test --coverage --coverage-html=coverage/html

# 生成文本报告
php artisan test --coverage --coverage-text

# 生成 XML 报告（CI/CD 使用）
php artisan test --coverage --coverage-clover=coverage.xml
```

### 报告位置

```
coverage/
├── html/           # HTML 报告
├── coverage.txt    # 文本报告
└── coverage.xml    # XML 报告
```

---

## 🔗 相关文档

- [测试方案总览](./01-test-strategy.md)
- [数据保护方案](./02-data-protection.md)
- [API 测试方案](./03-api-test.md)

---

**版本**: v1.0 | **更新日期**: 2026-04-30 | **作者**: P9 测试架构师
