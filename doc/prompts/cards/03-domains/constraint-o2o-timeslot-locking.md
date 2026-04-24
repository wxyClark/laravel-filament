# 领域约束：O2O 时间片冲突检测

## 用途说明
解决预约系统中核心的并发冲突问题，防止超卖。

## 适用场景
- 实现服务预约、会议室预定、票务系统等。

## 标准内容块
```markdown
## 算法约束：SQL 级并发锁
1. **查询锁定**：在检查时间片可用性时，必须对 `appointment_timeslots` 表使用 `lockForUpdate()`。
2. **容量校验**：逻辑判定为 `if (booked_count >= max_capacity) throw new TimeslotOccupiedException();`。
3. **重叠检测**：SQL 查询需覆盖时间段重叠的所有情况（开始时间在区间内、结束时间在区间内、包含区间）。

## 代码模式参考
```php
$slot = AppointmentTimeslot::where(...)
    ->lockForUpdate()
    ->firstOrFail();
```
```
