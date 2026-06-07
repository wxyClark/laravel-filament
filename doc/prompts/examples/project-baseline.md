# 项目基线模板 (Project Baseline Template)

> **版本**: v3.0 | **最后更新**: 2026-06-07

## 用途说明
项目首次对话时发送给 AI IDE 的基线信息模板。复制并修改为自己的项目配置，后续每次对话自动继承。

## 适用场景
- 首次对话加载项目上下文
- 新建对话窗口时恢复上下文
- 团队协作时统一项目认知

---

## 基线模板

```markdown
# 项目基线

## 技术栈
- **框架**: Laravel 12.x + Filament 3.x
- **PHP**: 8.4+
- **数据库**: MySQL 8.0
- **缓存**: Redis
- **队列**: Horizon
- **前端**: Blade + Alpine.js
- **测试**: Pest PHP
- **代码规范**: Pint + PHPStan Level 5

## 依赖包
- spatie/laravel-permission (权限管理)
- spatie/laravel-model-states (状态机)
- barryvdh/laravel-ide-helper (IDE 提示)
- laravel/telescope (调试监控)

## 认证方案
- 多守卫: customer (前台用户), admin (后台管理员)
- API 认证: Laravel Sanctum
- 密码重置: password_reset_tokens

## 代码约定
- **命名空间**: App\Domains\<Domain>\
- **DTO**: App\DTOs\<Domain>\
- **事件**: App\Events\<Domain>\
- **监听器**: App\Listeners\<Domain>\
- **Filament**: app/Filament/Resources/<Domain>/

## 已有模型
- Customer (app/Models) - 前台用户
- Admin (app/Models) - 后台管理员

## 已有模块
- 用户认证 (注册/登录/多守卫)
- Filament Admin Panel

## 数据库约定
- 所有表使用 `softDeletes()`
- 金额字段使用 `DECIMAL(12,4)`
- 关联字段使用 `foreignId()->constrained()`
- 删除策略优先 `restrictOnDelete()`

## 禁止事项
- 不使用 Kernel.php 配置中间件
- 不在控制器中写验证逻辑（使用 FormRequest）
- 不在 Blade 中写内联 CSS
- 不在方法中直接 new 依赖对象
- 不使用 Config/Cache/Mail Facade
```

---

## 项目基线填写清单

复制模板后，逐项填写：

### 技术栈（必填）
- [ ] 框架及版本
- [ ] PHP 版本
- [ ] 数据库及版本
- [ ] 缓存/队列方案

### 依赖包（按需填写）
- [ ] 认证包
- [ ] 权限包
- [ ] 其他核心包

### 代码约定（必填）
- [ ] 目录结构
- [ ] 命名空间规则
- [ ] 命名规则

### 已有资产（按需填写）
- [ ] 已有模型
- [ ] 已有模块
- [ ] 已有 Migration

### 项目规范（必填）
- [ ] 数据库约定
- [ ] 禁止事项

---

## 持久化方案

### Cursor (.cursorrules)
```
# 项目编码规则
# 加载 doc/prompts/examples/project-baseline.md 的内容
```

### Trae (.traerm)
```
# 项目规则
# 加载 doc/prompts/examples/project-baseline.md 的内容
```

### 手动加载
每次新建对话时，先发送基线内容。

---

**版本**: v3.0 | **最后更新**: 2026-06-07
