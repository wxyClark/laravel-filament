# Termux 本地运行手册（Runbook）

> 适用环境：Android Termux，项目在 `/sdcard/storage/emulated/0/www/laravel-filament`
> 用途：新会话/轮询时按本手册恢复上下文并执行操作。

## 1. 项目与数据位置

| 项 | 值 |
|---|---|
| 项目路径 | `/sdcard/storage/emulated/0/www/laravel-filament`（git 仓库） |
| 数据库 | SQLite `database/database.sqlite`（含 44,809 条地址 + 41,352 街道） |
| GitHub Pages 站 | `/sdcard/storage/emulated/0/www/wxyClark.github.io` |
| git 凭据 | 已配置 `~/.git-credentials`（token 认证，HTTPS push 免交互） |
| git 身份 | `wxyClark` / `28052209+user@users.noreply.github.com` |

## 2. 启动服务

```bash
cd /sdcard/storage/emulated/0/www/laravel-filament
nohup php artisan serve --host=0.0.0.0 --port=8000 > /data/data/com.termux/files/usr/tmp/opencode/serve.log 2>&1 &
```

- 管理后台：`http://localhost:8000/admin/login`
- 账号 `admin@example.com` / 密码 `admin123`（登录页已自动预填，直接点登录）
- 检查是否运行：`pgrep -f "artisan serve"`
- 重启前先 `pkill -f "artisan serve"`（端口占用会导致新进程不监听）

关键环境事实（踩过的坑）：
- `/sdcard` 是 FUSE，不支持 `LOCK_EX` → 已改 `SESSION_DRIVER=database`、`CACHE_DRIVER=database`，勿改回 file
- `ext-sodium` 缺失，composer install 需要 `--ignore-platform-req=ext-sodium`
- 仓库缺失的 13 个资源 Pages 类与 20 张表（admin_notifications、cross_sell_*）已补迁移生成
- 大内存命令统一加 `-d memory_limit=512M`（默认 128M 会 OOM）

## 3. 地址数据初始化（多平台兼容）

地址表 `addresses` 已支持多数据源：
- `area_code`：民政部标准码（省/市/区 6 位、乡镇 9 位），**跨平台统一身份**，upsert 主键
- `code`：来源平台原始编码（pcas 2/4 位、高德 6 位）
- `source` / `synced_at`：来源与同步时间

### 3.1 在线初始化（推荐，免 key、每次拿最新数据）

```bash
cd /sdcard/storage/emulated/0/www/laravel-filament
php -d memory_limit=512M artisan addresses:init                 # 默认 Gitee 民政部数据源
php -d memory_limit=512M artisan addresses:init --flush          # 清空重导
php -d memory_limit=512M artisan addresses:init --unit=44        # 只导广东省（调试）
php -d memory_limit=512M artisan addresses:init --source=amap   # 高德接口源（需 .env: AMAP_KEY）
```

- 数据源：Gitee `modood/Administrative-divisions-of-China`（`dist/pcas-code.json` 31 省 × 3 级 + `dist/streets.csv` 乡镇 41,352 条 + 代码内置港澳台）
- **分页**：按省级单位（34 页）逐页 upsert；**断点续传**：进度存 `storage/app/address-import/state.json`，中断后重跑自动跳过已完成单位；上游数据修订号变化时自动重置断点
- 换数据源（pcas ↔ amap）后先 `--flush`，避免编码体系混杂

### 3.2 离线兜底

```bash
php -d memory_limit=512M artisan db:seed --class=AddressSeeder --force
php artisan addresses:import database/seeders/addresses.json   # 旧命令，文件导入
```

### 3.3 多平台来源说明

| 平台 | 免费 | 免 key | 接入方式 |
|---|---|---|---|
| Gitee pcas（默认） | ✅ | ✅ | 已实现 `pcas` 源 |
| 高德 AMap | 5000次/日 | ❌ 需 AMAP_KEY | 已实现 `amap` 源 |
| ShowAPI/聚合/易源 | 有免费档 | ❌ 需注册 AppKey | 按 `AddressProvider` 接口扩展 |
| 民政部官网/国家地名信息库 | — | — | 无机器可读 API，不适用 |

## 4. 终端字体（Termux + fontconfig）

| 用途 | 字体 | 位置 |
|---|---|---|
| 英文等宽 | Source Code Pro | `~/.local/share/fonts/SourceCodePro-{Regular,Bold}.ttf` ✅ 已装 |
| 中文（等宽黑体） | Sarasa Mono SC（更纱） | 下载中，见 §4.1 |
| 中文（楷体，可选） | LXGW WenKai Mono（霞鹜文楷） | 未装，命令见 §4.2 |

配置链：`termux.properties font-family=Source Code Pro` → fontconfig `~/.config/fontconfig/fonts.conf` 中 "Source Code Pro" 自动追加回退 "Sarasa Mono SC"。
**生效方式**：强制停止 Termux 应用再打开（`termux-reload-settings` 只重载部分属性）。

### 4.1 Sarasa 下载状态检查（轮询用）

```bash
tail -5 /data/data/com.termux/files/usr/tmp/opencode/dl.log
ls -la /data/data/com.termux/files/usr/tmp/opencode/sarasa_mono_sc.7z   # 完成=49736292 字节
ls /data/data/com.termux/files/usr/tmp/opencode/sarasa_done 2>/dev/null && echo "Sarasa 已解压完成"
```
后台脚本 `font_dl_extract.sh` 会自动续传并在完成后解压到 `~/.local/share/fonts/`。
若卡死重跑：`nohup /data/data/com.termux/files/usr/tmp/opencode/font_dl_extract.sh &`

### 4.2 楷体备选（霞鹜文楷 Mono，24.8MB，OFL 开源）

```bash
curl -L -o ~/.local/share/fonts/LXGWWenKaiMono-Regular.ttf \
  "https://github.com/lxgw/LxgwWenKai/releases/download/v1.530/LXGWWenKaiMono-Regular.ttf"
# 在 ~/.config/fontconfig/fonts.conf 的 <match> 中把 "LXGW WenKai Mono" 加进回退链（可放 Sarasa 前/后）
# 然后重启 Termux 应用
```

## 5. Git 提交

```bash
cd /sdcard/storage/emulated/0/www/laravel-filament
git add -A && git commit -m "..." && git push
```
注意：`.env` 含预填账号密码，已被 gitignore，勿强制提交。
