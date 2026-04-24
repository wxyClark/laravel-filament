# 🚀 实战演练：从需求到代码的自动化流

## 场景背景
假设你现在要实现一个 **“O2O 门店服务预约”** 功能，核心难点在于**防止同一时间段被多人重复预约**。

## 步骤 1: 调用母提示词
在 Lingma 中发送 `02-meta-prompt-template.md` 中的内容，激活“提示词自动组装引擎”。

## 步骤 2: 输入自然语言需求
> “我要开发一个 O2O 预约功能，核心是检查时间片可用性，必须防止并发冲突。”

## 步骤 3: AI 自动组装出的“超级提示词”
AI 会根据你的需求，从 `cards` 库中抓取以下内容并组合：

---
**(以下是 AI 生成的示例输出)**

# 任务：实现 O2O 预约时间片冲突检测服务

## 1. 项目上下文注入
- **当前路径**: app/Services/AppointmentService.php
- **技术栈**: Laravel 12, Filament 3.x
- **数据库现状**: 已存在 `appointment_timeslots` 表，包含 `max_capacity` 和 `booked_count` 字段。

## 2. 核心原则
- **类型安全**: 所有方法必须有严格类型声明。
- **事务一致性**: 涉及库存变动的操作必须包裹在 `DB::transaction()` 中。

## 3. 角色设定
@TradeEngineer (交易工程师)
你是一位精通高并发处理的专家，专注于数据一致性与锁机制。

## 4. 领域约束 (O2O 预约)
- **SQL 级锁**: 在检查时间片时，必须对 `appointment_timeslots` 表使用 `lockForUpdate()`。
- **容量校验**: 逻辑判定为 `if (booked_count >= max_capacity) throw new TimeslotOccupiedException();`。

## 5. 任务指令
请在 `AppointmentService` 中实现 `checkAndBookSlot(int $slotId)` 方法：
1. 开启事务。
2. 锁定并查询时间片。
3. 校验容量。
4. 增加 `booked_count`。
5. 提交事务或回滚。

---

## 步骤 4: 执行与验证
将上述 AI 生成的“超级提示词”发送给 Lingma，它将直接为你生成一段带有 `lockForUpdate()` 和完整异常处理的 PHP 代码。

## 总结
通过这套体系，你不再需要手动去翻找各种规范文档，**AI 会自动根据你的需求，从知识库中调取最专业的约束条件**，从而保证生成的代码在生产环境中也是健壮的。
