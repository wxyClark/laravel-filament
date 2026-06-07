# 上下文注入：项目元数据感知 (Project Metadata Injection)

> **版本**: v3.0 | **层級**: L0 | **最后更新**: 2026-06-07

## 用途说明
在执行任务前，强制 AI 扫描项目现有结构，避免产生幻觉或重复定义。

## 适用场景
- 任何涉及数据库迁移、模型关联或路由配置的任务开始前
- 创建新功能模块前

## 标准内容块
```markdown
## 项目上下文注入指令

请在生成代码前，通过以下指令获取项目现状：
- **技术栈版本**：Laravel {{laravel_version}}, Filament {{filament_version}}
- **现有模型**：`@list_dir('app/Models')`
- **数据库 Schema**：`@run_in_terminal('php artisan db:show --json')`
- **依赖包**：检查 `composer.json` 确认是否已安装所需扩展
- **已有路由**：`@list_dir('routes/')`
- **已有 Migration**：`@list_dir('database/migrations')`

## 上下文获取清单
- [ ] 确认目标表是否已存在
- [ ] 确认目标模型是否已存在
- [ ] 确认相关依赖是否已安装
- [ ] 确认命名空间是否正确
- [ ] 确认是否有冲突的 Migration
```
```
