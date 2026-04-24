# 📖 RBAC 权限模块 - 用户故事

> **L3: 用户故事层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "rbac"
document_type: "user_stories"
version: "1.0"
total_stories: 6
priority_distribution:
  P0: 4
  P1: 2
```

---

## 🎯 US-RBAC-001: 角色管理

**作为** 系统管理员  
**我希望** 能够创建和管理系统角色  
**以便** 为不同用户分配适当的权限

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建角色"
    given: "管理员进入角色管理页面"
    when: "填写角色名称、描述、选择权限"
    then: "角色创建成功，显示在角色列表"

  - scenario: "编辑角色权限"
    given: "管理员编辑角色"
    when: "修改角色权限"
    then: "权限更新成功，已关联用户权限同步更新"

  - scenario: "删除角色"
    given: "角色没有关联用户"
    when: "管理员删除角色"
    then: "角色删除成功"

  - scenario: "删除有用户的角色"
    given: "角色有关联用户"
    when: "管理员删除角色"
    then: "提示无法删除，需要先移除用户"

  - scenario: "角色名称唯一"
    given: "已存在角色名 '管理员'"
    when: "创建角色名 '管理员'"
    then: "提示角色名已存在"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Role
    - Permission
  tables:
    - roles
    - role_has_permissions
  policies:
    - "角色删除前检查关联用户"
    - "角色权限变更触发用户权限缓存清除"
  filament_resource: "RoleResource"
```

---

## 🎯 US-RBAC-002: 权限管理

**作为** 系统管理员  
**我希望** 能够定义和管理系统权限  
**以便** 精细化控制用户操作权限

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "查看权限列表"
    given: "管理员进入权限管理页面"
    when: "查看权限列表"
    then: "按模块分组显示所有权限"

  - scenario: "创建权限"
    given: "管理员创建新权限"
    when: "填写权限名称、标识、描述、所属模块"
    then: "权限创建成功"

  - scenario: "编辑权限"
    given: "管理员编辑权限"
    when: "修改权限信息"
    then: "权限更新成功"

  - scenario: "权限标识唯一"
    given: "已存在权限标识 'users.create'"
    when: "创建权限标识 'users.create'"
    then: "提示权限标识已存在"

  - scenario: "权限模块分组"
    given: "权限按模块分组"
    when: "查看权限列表"
    then: "显示模块分组（用户管理、角色管理、订单管理等）"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Permission
  tables:
    - permissions
  modules:
    - users
    - roles
    - orders
    - products
    - finance
    - reports
  filament_resource: "PermissionResource"
```

---

## 🎯 US-RBAC-003: 用户角色分配

**作为** 系统管理员  
**我希望** 能够为用户分配角色  
**以便** 控制用户的系统访问权限

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "分配角色给用户"
    given: "管理员查看用户详情"
    when: "选择角色并保存"
    then: "用户获得角色权限，立即生效"

  - scenario: "批量分配角色"
    given: "管理员选择多个用户"
    when: "批量分配角色"
    then: "所有选中用户获得角色权限"

  - scenario: "移除用户角色"
    given: "用户有多个角色"
    when: "移除其中一个角色"
    then: "用户失去该角色权限，保留其他角色权限"

  - scenario: "角色变更通知"
    given: "用户角色被修改"
    when: "角色变更生效"
    then: "用户收到角色变更通知"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - User
    - Role
  tables:
    - model_has_roles
  implementation:
    - "使用 Spatie Permission 包"
    - "角色变更清除用户权限缓存"
    - "支持多角色分配"
  events:
    - UserRoleAssigned
    - UserRoleRevoked
```

---

## 🎯 US-RBAC-004: 权限验证与守卫

**作为** 系统  
**我希望** 能够验证用户权限并阻止未授权访问  
**以便** 保护系统资源安全

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "API 权限验证"
    given: "用户请求受保护 API"
    when: "用户没有对应权限"
    then: "返回 403 Forbidden"

  - scenario: "页面访问守卫"
    given: "用户访问管理页面"
    when: "用户没有对应权限"
    then: "显示无权限提示或重定向"

  - scenario: "按钮级权限控制"
    given: "用户查看列表页面"
    when: "用户没有创建权限"
    then: "隐藏创建按钮"

  - scenario: "超级管理员绕过"
    given: "用户是超级管理员"
    when: "验证任何权限"
    then: "返回 true，允许访问"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - User
    - Role
    - Permission
  implementation:
    - "中间件: auth, permission"
    - "策略类: Policy classes"
    - "Blade 指令: @can, @role"
    - "Filament 页面守卫: getCanAccess()"
  middleware:
    - "App\Http\Middleware\PermissionMiddleware"
  policies:
    - "App\Policies\*Policy"
```

---

## 🎯 US-RBAC-005: 操作日志审计

**作为** 系统管理员  
**我希望** 能够查看用户的操作日志  
**以便** 追踪系统操作记录，满足审计需求

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "记录操作日志"
    given: "用户执行关键操作"
    when: "操作完成"
    then: "记录操作者、操作类型、操作对象、操作时间"

  - scenario: "查看操作日志"
    given: "管理员进入操作日志页面"
    when: "筛选日志"
    then: "显示符合条件的操作日志列表"

  - scenario: "日志详情"
    given: "管理员查看日志详情"
    when: "点击日志记录"
    then: "显示操作详情，包括变更前后数据"

  - scenario: "日志导出"
    given: "管理员导出日志"
    when: "选择时间范围和导出格式"
    then: "导出 Excel/CSV 格式的日志文件"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - ActivityLog
  tables:
    - activity_logs
  implementation:
    - "使用 Spatie Activitylog 包"
    - "自动记录模型变更"
    - "支持自定义日志"
  fields:
    - log_name
    - description
    - subject_type
    - subject_id
    - causer_type
    - causer_id
    - properties (JSON)
    - created_at
```

---

## 🎯 US-RBAC-006: 登录安全控制

**作为** 系统管理员  
**我希望** 能够配置登录安全策略  
**以便** 防止未授权访问和暴力破解

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "登录失败锁定"
    given: "用户连续登录失败"
    when: "失败次数达到阈值（5次）"
    then: "账户锁定 30 分钟"

  - scenario: "异地登录提醒"
    given: "用户在新设备/IP 登录"
    when: "登录成功"
    then: "发送登录提醒通知"

  - scenario: "强制下线"
    given: "管理员强制用户下线"
    when: "执行强制下线操作"
    then: "用户会话失效，需要重新登录"

  - scenario: "密码过期策略"
    given: "密码超过 90 天未修改"
    when: "用户登录"
    then: "提示修改密码"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - User
    - LoginAttempt
    - UserSession
  tables:
    - login_attempts
    - user_sessions
  configuration:
    max_attempts: 5
    lockout_duration: 30
    password_expiry_days: 90
  notifications:
    - LoginSuccessNotification
    - SuspiciousLoginNotification
    - PasswordExpiryNotification
```

---

## 📊 用户故事汇总

| 故事ID | 优先级 | 复杂度 | 关联实体 |
|--------|--------|--------|---------|
| US-RBAC-001 | P0 | 中 | Role, Permission |
| US-RBAC-002 | P0 | 低 | Permission |
| US-RBAC-003 | P0 | 中 | User, Role |
| US-RBAC-004 | P0 | 高 | User, Role, Permission |
| US-RBAC-005 | P1 | 中 | ActivityLog |
| US-RBAC-006 | P1 | 中 | User, LoginAttempt |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
