# 01-PM.md 快速改动草案

- 目标：在不改变核心结构的前提下，尽快提升机器可读性与落地性，便于团队快速实现。

- 核心改动清单（最小可行改动）
  - 增加元数据头：OutputFormat、Version、Author、Date、Source 等字段，放在文档顶部以便 downstream 解析。
  - Domain Models 的结构化字段描述模板：为每个实体定义字段元数据对象的统一格式（JSON/YAML），包含：name, type, required, unique, default, constraints, relation, note, sample。
  - API Contracts 的模板示例：提供一个最小的请求/响应示例，包含一个核心接口如 POST /api/posts，以及相应的字段与状态码。
  - State Machine 的文本化描述模板：提供状态、事件和条件的简单文本描述，便于后续自动化生成图形。
  - Filament UI 示例：给出一个简单的列配置和筛选器映射的片段，示例以 Post 资源为准。
  - 输出示例模板：为 Domain Models、API Contracts、State Machine、Admin UI 提供最小可用示例，确保团队对齐。
- 版本化与回退策略
  - 引入版本标识，若下游模板缺失版本，提供简化输出或清单供人工确认。
- 进阶改动（后续迭代）
  - 引入机器可执行的 OpenAPI/JSON Schema 语言描述。
  - 针对不同领域模型扩展可选字段（例如 audit fields、soft deletes 等）。

- 实施路线（阶段性）
  1) 在 prompts/advice/ 中新增快速改动草案文件。 
  2) 为每个核心部分创建最小可用的示例输出模板。 
  3) 与现有下游模板建立简单的存在性检查（版本号存在与否）。
  4) 收集团队反馈后，迭代扩展至结构化 schema。 

- 评估衡量
  - 是否能被现有工作流直接消费？是否减少人工对齐成本？输出的一致性是否提升？
