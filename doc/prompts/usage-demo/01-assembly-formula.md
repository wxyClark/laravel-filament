# 🧩 提示词组装公式 (Prompt Assembly Formula)

## 设计理念
为了让 AI 像乐高积木一样精准拼装提示词，我们采用**五层认知模型**进行组装。这种结构能确保 AI 在生成代码时，既懂项目现状，又守质量底线，还能处理复杂业务逻辑。

## 组装公式
> **完整 Prompt = [L0: 元数据感知] + [L1: 核心原则] + [L3: 角色注入] + [L2: 领域约束] + [L4: 任务指令]**

### 各层级说明

| 层级 | 名称 | 作用 | 示例卡片 |
| :--- | :--- | :--- | :--- |
| **L0** | **Meta-Data (元数据)** | 告诉 AI “我在哪”、“项目现状如何”，减少幻觉。 | `project-metadata-injection.md` |
| **L1** | **Principles (原则)** | 设定代码质量的底线，如类型安全、TDD。 | `type-safety-immutability.md` |
| **L3** | **Roles (角色)** | 赋予 AI 专业身份，激活特定领域的知识库。 | `@ProductArchitect`, `@TradeEngineer` |
| **L2** | **Domains (领域)** | 注入特定业务的算法或逻辑约束（深水区）。 | `constraint-o2o-timeslot-locking.md` |
| **L4** | **Tasks (任务)** | 明确具体的产出物格式和要求。 | `template-service-layer.md` |

## 实际操作示例
如果你想开发一个“O2O 预约服务”，你的组装逻辑应该是：
1.  **L0**: 先让 AI 扫描现有的 `appointments` 表结构。
2.  **L1**: 强制要求使用严格类型和事务保护。
3.  **L3**: 召唤 `@TradeEngineer` 处理并发逻辑。
4.  **L2**: 插入 `constraint-o2o-timeslot-locking.md` 防止超卖。
5.  **L4**: 使用 `template-service-layer.md` 生成 Service 代码。

---
**下一步**：请参考 `02-meta-prompt-template.md` 学习如何让 AI 自动完成这个组装过程。
