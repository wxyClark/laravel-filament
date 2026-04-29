# 📘 最佳用法四：AI IDE 集成指南

> **Lingma / Trae / Cursor 集成** | **提示词库配置** | **工作流优化**

---

## 📋 概述

本文档说明如何将提示词库集成到主流 AI IDE 中，实现高效的开发工作流。

---

## 🎯 Lingma（通义灵码）集成

### 配置方式

```json
// .mcp.json
{
    "mcpServers": {
        "laravel-boost": {
            "command": "php",
            "args": ["artisan", "boost:mcp"]
        }
    }
}
```

### 提示词使用方式

```markdown
# 在 Lingma 中使用提示词

## 方式 1：直接引用文件
@doc/prompts/cards/01-roles/trade-engineer.md

## 方式 2：使用 @ 符号
@TradeEngineer

## 方式 3：组合使用
@doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-001
@template-service-layer
@constraint-inventory-concurrency
```

### 推荐工作流

```
1. 打开 AI 聊天窗口
2. 输入: @doc/PRD/{module}/stories/01-user-stories.md#{story_id}
3. 输入: 基于上述用户故事，实现 {功能}
4. AI 自动生成代码
5. 验证并迭代
```

---

## 🎯 Trae CN 集成

### 配置方式

```markdown
# 在 Trae 中使用提示词

## 方式 1：背景注入
在对话开始时输入：
@doc/prompts/cards/00-core/type-safety-immutability.md
@doc/prompts/cards/02-context/filament-best-practices.md

## 方式 2：任务描述
基于 @doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-001
实现商品列表功能

## 方式 3：角色设定
你是 @TradeEngineer，请帮我实现订单创建功能
```

### 推荐工作流

```
1. 新建对话
2. 注入上下文: @doc/prompts/cards/02-context/
3. 注入角色: @doc/prompts/cards/01-roles/{role}.md
4. 描述任务: 基于 @doc/PRD/{module}/...
5. 执行生成
```

---

## 🎯 Cursor 集成

### 配置方式

```markdown
# 在 Cursor 中使用提示词

## 方式 1：@ 符号引用
@00-core/type-safety-immutability.md
@01-roles/TradeEngineer.md
@doc/PRD/01-ecommerce/stories/01-user-stories.md

## 方式 2：Composer 功能
在 Composer 中输入：
- 角色: @TradeEngineer
- 上下文: @doc/PRD/01-ecommerce/
- 任务: 实现订单创建服务

## 方式 3：代码注释引导
/**
 * Context: Laravel 12 + Filament 3.x
 * Role: TradeEngineer
 * Task: Implement OrderService
 * Reference: doc/PRD/01-ecommerce/
 */
```

### 推荐工作流

```
1. 打开 Composer
2. 添加上下文: @doc/PRD/{module}/
3. 添加角色: @01-roles/{role}.md
4. 添加原则: @00-core/type-safety-immutability.md
5. 描述任务
6. 生成代码
```

---

## 📊 提示词库使用统计

### 常用提示词碎片 TOP 10

| 排名 | 碎片名称 | 使用频率 | 适用场景 |
|------|---------|---------|---------|
| 1 | @type-safety-immutability | 极高 | 所有场景 |
| 2 | @TradeEngineer | 极高 | 订单/支付 |
| 3 | @template-service-layer | 高 | 服务层实现 |
| 4 | @dependency-injection | 高 | 依赖注入 |
| 5 | @event-driven | 高 | 事件驱动 |
| 6 | @FilamentUIDesigner | 高 | 后台页面 |
| 7 | @template-filament-resource | 高 | Filament 资源 |
| 8 | @template-dto-conversion | 中 | DTO 转换 |
| 9 | @template-form-request | 中 | 表单验证 |
| 10 | @constraint-inventory-concurrency | 中 | 库存并发 |

---

## 🚀 快速启动

### 步骤 1：克隆提示词库

```bash
# 提示词库位于 doc/prompts/cards/
ls doc/prompts/cards/
```

### 步骤 2：配置 AI IDE

根据使用的 IDE 选择对应配置方式。

### 步骤 3：开始使用

```markdown
# 第一个提示词

@doc/prompts/cards/01-roles/trade-engineer.md

基于 @doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-001
实现商品列表功能
```

---

**版本**: v1.0 | **更新日期**: 2026-04-27
