# 日志模块文档

> **文档编号**: DEV-LOG-001
> **创建日期**: 2026-07-18
> **状态**: 已实现

---

## 1. 模块概述

日志模块提供统一的请求日志和业务日志记录功能，支持 requestId 追踪，便于问题定位和上下文分析。

---

## 2. 核心功能

| 功能 | 说明 | 状态 |
|------|------|------|
| 请求日志 | 记录每次 API 请求的完整信息 | ✅ |
| 业务日志 | 记录业务逻辑中的 info/warning/error | ✅ |
| requestId 追踪 | 自动生成唯一请求 ID，贯穿整个请求链路 | ✅ |
| 敏感信息过滤 | 自动过滤密码、Token 等敏感字段 | ✅ |
| 客户端识别 | 自动识别 Web/API/Postman/cURL 等客户端类型 | ✅ |
| Filament 管理界面 | 通过后台查看和筛选日志 | ✅ |
| 辅助函数 | 提供 log_info/log_warning/log_error 等快捷函数 | ✅ |

---

## 3. 数据库表

### 3.1 request_logs (请求日志)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| request_id | varchar(36) | 请求唯一标识 (UUID) |
| method | varchar(10) | HTTP 方法 |
| path | varchar(500) | 请求路径 |
| controller | varchar(200) | 控制器 |
| action | varchar(100) | 方法 |
| user_type | varchar(100) | 用户类型 |
| user_id | bigint | 用户 ID |
| user_name | varchar(100) | 用户名 |
| ip_address | varchar(45) | IP 地址 |
| client_type | varchar(50) | 客户端类型 |
| user_agent | text | User Agent |
| request_headers | json | 请求 Headers |
| request_body | json | 请求 Body |
| query_params | json | Query 参数 |
| response_status | smallint | 响应状态码 |
| response_body | json | 响应 Body |
| response_time | int | 响应时间 (ms) |
| memory_usage | int | 内存使用 (bytes) |
| exception_class | varchar(200) | 异常类名 |
| exception_message | text | 异常消息 |
| exception_trace | text | 异常堆栈 |

### 3.2 business_logs (业务日志)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| request_id | varchar(36) | 关联的请求 ID |
| level | varchar(20) | 日志级别 (debug/info/warning/error/critical) |
| channel | varchar(50) | 日志通道 |
| message | text | 日志消息 |
| context | json | 上下文数据 |
| extra | json | 额外数据 |
| file | varchar(500) | 触发文件 |
| line | int | 触发行号 |
| trace | text | 调用堆栈 |

---

## 4. 使用方法

### 4.1 自动记录请求日志

请求日志通过中间件自动记录，无需手动调用。中间件已注册到 API 中间件组：

```php
// bootstrap/app.php
$middleware->group('api', [
    \App\Infrastructure\Http\Middleware\RequestLogging::class,
]);
```

### 4.2 记录业务日志

#### 使用辅助函数 (推荐)

```php
// 记录信息日志
log_info('用户登录成功', ['user_id' => $user->id]);

// 记录警告日志
log_warning('库存不足', ['product_id' => $product->id, 'stock' => $stock]);

// 记录错误日志
log_error('支付失败', ['order_id' => $order->id, 'error' => $e->getMessage()]);

// 记录严重错误日志
log_critical('系统异常', ['error' => $e->getMessage()]);
```

#### 使用 Facade

```php
use App\Domains\Logging\Facades\Log;

Log::info('用户登录成功', ['user_id' => $user->id]);
Log::warning('库存不足', ['product_id' => $product->id]);
Log::error('支付失败', ['order_id' => $order->id]);
```

#### 使用 Service

```php
use App\Domains\Logging\Services\LogService;

LogService::info('用户登录成功', ['user_id' => $user->id]);
LogService::warning('库存不足', ['product_id' => $product->id]);
```

### 4.3 获取 requestId

```php
// 在请求中获取 requestId
$requestId = get_request_id();

// 或者从请求头获取
$requestId = request()->header('X-Request-ID');
```

### 4.4 查询日志

```php
use App\Domains\Logging\Models\RequestLog;
use App\Domains\Logging\Models\BusinessLog;
use App\Domains\Logging\Services\LogService;

// 根据 requestId 查询请求日志
$requestLog = RequestLog::findByRequestId($requestId);

// 根据 requestId 查询业务日志
$businessLogs = BusinessLog::findByRequestId($requestId);

// 获取请求和关联的业务日志
$logs = LogService::getLogsByRequestId($requestId);

// 获取日志统计
$stats = LogService::getStats(24); // 最近 24 小时
```

---

## 5. Filament 管理界面

### 5.1 请求日志

路径: `System > Request Logs`

功能:
- 查看所有请求日志列表
- 按请求方法、客户端类型、状态码筛选
- 仅显示异常请求
- 仅显示慢请求 (>1s)
- 查看请求详情 (Headers、Body、响应)
- 查看关联的业务日志
- 复制 requestId

### 5.2 业务日志

路径: `System > Business Logs`

功能:
- 查看所有业务日志列表
- 按日志级别筛选 (info/warning/error/critical)
- 查看日志详情 (上下文、触发位置、调用堆栈)
- 查看关联的请求日志

---

## 6. 客户端类型识别

| 客户端类型 | 识别方式 |
|------------|----------|
| web | 默认 Web 请求 |
| api | API 请求 (expectsJson) |
| ajax | XMLHttpRequest |
| postman | User-Agent 包含 postman |
| insomnia | User-Agent 包含 insomnia |
| curl | User-Agent 包含 curl |
| android | User-Agent 包含 android |
| ios | User-Agent 包含 iphone/ipad |

---

## 7. 敏感信息过滤

### 7.1 请求 Headers 过滤

以下 Headers 会被替换为 `[REDACTED]`:
- authorization
- cookie
- x-csrf-token

### 7.2 请求 Body 过滤

以下字段会被替换为 `[REDACTED]`:
- password
- password_confirmation
- token
- secret
- api_key

---

## 8. 文件结构

```
app/Domains/Logging/
├── Enums/
│   └── LogLevel.php              # 日志级别枚举
├── Facades/
│   └── Log.php                   # 日志 Facade
├── Models/
│   ├── RequestLog.php            # 请求日志模型
│   └── BusinessLog.php           # 业务日志模型
└── Services/
    └── LogService.php            # 日志服务

app/Infrastructure/
├── Http/Middleware/
│   └── RequestLogging.php        # 请求日志中间件
└── Filament/Resources/
    ├── LoggingResource.php       # 请求日志 Filament 资源
    ├── LoggingResource/Pages/
    │   ├── ListLogging.php       # 列表页
    │   └── ViewLogging.php       # 详情页
    ├── BusinessLogResource.php   # 业务日志 Filament 资源
    └── BusinessLogResource/Pages/
        ├── ListBusinessLogs.php  # 列表页
        └── ViewBusinessLog.php   # 详情页

database/migrations/
├── 2026_07_18_000001_create_request_logs_table.php
└── 2026_07_18_000002_create_business_logs_table.php

app/helpers.php                   # 辅助函数
```

---

## 9. 后续优化

- [ ] 添加日志清理定时任务 (保留 90 天)
- [ ] 添加日志导出功能
- [ ] 添加实时日志流 (WebSocket)
- [ ] 添加日志告警规则 (如: 错误率 > 5% 时通知)
