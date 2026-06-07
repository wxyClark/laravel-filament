# 领域约束：O2O 时间片冲突检测 (O2O Timeslot Locking)

> **版本**: v3.0 | **层級**: L2+ | **最后更新**: 2026-06-07

## 用途说明
解决预约系统中核心的并发冲突问题，防止超卖。

## 适用场景
- 服务预约、会议室预定、票务系统等
- 时段拆分、服务时长动态配置

## 标准内容块
```markdown
## 算法约束：SQL 级并发锁

### 1. 查询锁定
检查时间片可用性时，必须对预约表使用 `lockForUpdate()`。

```php
$slot = AppointmentTimeslot::where('service_id', $serviceId)
    ->where('start_time', '<=', $requestedEnd)
    ->where('end_time', '>', $requestedStart)
    ->lockForUpdate()
    ->firstOrFail();
```

### 2. 容量校验
```php
if ($slot->booked_count >= $slot->max_capacity) {
    throw new TimeslotOccupiedException($slot->id);
}
```

### 3. 重叠检测
SQL 覆盖所有时间段重叠情况：
- 请求的起始时间在已有区间内
- 请求的结束时间在已有区间内
- 请求包含已有区间
- 已有区间包含请求区间

### 4. 幂等性
预约操作使用 `unique(customer_id, timeslot_id)` 唯一索引防止重复预约。
```
```
