# 初始化项目

## 1. 创建laravel12项目
```bash
composer create-project laravel/laravel laravel-filament
```

## 2. 安装filament
```bash
composer require filament/filament
```

## 3 . 安装laravel-boost并加载skills

- 为当前项目安装laravel-boost 
```bash
composer require laravel-boost/laravel-boost
```

- 为laravel-boost 加载 laravel官方 top10的 skills

```danger
为Boost框架集成Filament渲染引擎制定一套全面的最佳实践指南。
该指南应涵盖环境配置、依赖管理、初始化流程、资源加载、渲染性能优化、内存管理、跨平台兼容性处理、错误处理与调试等关键环节。
需提供具体的实现步骤、代码示例、性能测试指标及常见问题解决方案，确保集成过程高效稳定且符合行业标准。

符合 Laravel 和 Filament 官方最佳实践
```

- 加载skills 覆盖了以下开发规范，包括：

| Skill | 用途 | 权威性 | 特点 |
|-------|------|--------|------|
| **laravel-best-practices** | Laravel 全栈最佳实践 | ⭐⭐⭐ 官方出品 | 涵盖 Eloquent、路由、验证、缓存、安全等 18 个规则文件 |
| **laravel-architecture** | 架构设计模式 | ⭐⭐⭐ 官方出品 | 包含瘦 Controller、Service、Repository、Model 完整示例 |
| **restful-api-routing** | RESTful API 设计规范 | ⭐⭐⭐ 官方出品 | 覆盖版本控制、资源路由、响应格式、查询参数 |
| **mysql-best-practices** | MySQL 数据库优化 | ⭐⭐⭐ 官方出品 | 索引策略、查询优化、Eloquent 模式、连接管理 |
| **redis-best-practices** | Redis 缓存实战 | ⭐⭐⭐ 官方出品 | 缓存模式、会话管理、限流、分布式锁、队列 |
| **queue-jobs-best-practices** | 队列任务处理 | ⭐⭐⭐ 官方出品 | 重试策略、失败处理、批量操作、Horizon 监控 |
| **php-development** | PHP 8.2+ 开发规范 | ⭐⭐⭐ 官方出品 | 类型安全、现代特性、错误处理、性能优化 |
| **pint-code-style** | 代码风格检查 | ⭐⭐⭐ 官方出品 | PSR-12 标准、Laravel 约定、自动格式化 |
| **pest-testing** | 现代化 PHP 测试 | ⭐⭐⭐ 官方出品 | 简洁语法、内置助手、并行执行、快照测试 |
| **livewire-development** | Livewire 组件开发 | ⭐⭐⭐ 官方出品 | 响应式组件、属性同步、生命周期钩子 |
| **inertia-react-development** | Inertia + React 全栈 | ⭐⭐⭐ 官方出品 | SSR 支持、客户端导航、类型安全 |
| **inertia-vue-development** | Inertia + Vue 全栈 | ⭐⭐⭐ 官方出品 | 组件共享、懒加载、性能优化 |
| **tailwindcss-development** | Tailwind CSS 样式 | ⭐⭐⭐ 官方出品 | 原子化 CSS、响应式设计、暗色模式 |
| **fluxui-development** | Flux UI 组件开发 | ⭐⭐ 社区出品 | 现代化 UI 组件、TypeScript 支持 |
| **mcp-development** | MCP 协议开发 | ⭐⭐⭐ 官方出品 | AI 工具集成、标准化接口 |
| **pennant-development** | 功能开关管理 | ⭐⭐⭐ 官方出品 | A/B 测试、渐进式发布 |
| **wayfinder-development** | 导航路由管理 | ⭐⭐⭐ 官方出品 | 嵌套路由、权限控制 |

### Skills 特点总结

- **官方权威**：17 个 Skills 中 15 个来自 Laravel/PHP 官方团队
- **全面覆盖**：涵盖后端、前端、数据库、缓存、队列、测试、安全
- **实战导向**：每个 Skill 都包含具体代码示例和最佳实践
- **持续更新**：随 Laravel 生态演进保持同步更新

## 3. 安装filament
```bash
composer require filament/filament
```

## 4. 安装 Portaine 管理docker容器
