# 跨境电商系统部署架构文档

## 一、系统概览

本项目包含 4 个独立子系统，每个系统对应一个域名，独立部署：

| 系统 | 域名 | 说明 |
|------|------|------|
| CrossSell 商城 | `shop.example.com` | 客户端商城（前台） |
| CrossSell 后台 | `admin.example.com` | 管理后台（运营） |
| 薪福通 | `hr.example.com` | HR/薪资系统 |
| 权限管理 | `super.example.com` | 超级管理员后台 |

---

## 二、系统架构

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            Nginx 反向代理                                    │
│                     (SSL 终止 + 域名路由 + 负载均衡)                         │
└─────────────────────────────────────────────────────────────────────────────┘
        │                │                │                │
        ▼                ▼                ▼                ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ shop.        │ │ admin.       │ │ hr.          │ │ super.       │
│ example.com  │ │ example.com  │ │ example.com  │ │ example.com  │
│              │ │              │ │              │ │              │
│ CrossSell    │ │ CrossSell    │ │ Xinfutong    │ │ Permission   │
│ 商城前端     │ │ 管理后台     │ │ HR系统       │ │ 超级管理     │
└──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
        │                │                │                │
        ▼                ▼                ▼                ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         Laravel Application                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │ CrossSell   │  │ Xinfutong   │  │ Base        │  │ Auth        │       │
│  │ Module      │  │ Module      │  │ Module      │  │ Module      │       │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘       │
└─────────────────────────────────────────────────────────────────────────────┘
        │                │                │                │
        ▼                ▼                ▼                ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          数据库集群                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │ crosssell_db│  │ xinfutong_db│  │ base_db     │  │ auth_db     │       │
│  │ (商城数据)  │  │ (HR数据)    │  │ (基础数据)  │  │ (权限数据)  │       │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘       │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 三、Nginx 配置

### 3.1 主配置文件

```nginx
# /etc/nginx/nginx.conf

user www-data;
worker_processes auto;
pid /run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    # Gzip 压缩
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    # 日志格式
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;
    error_log /var/log/nginx/error.log;

    # 包含站点配置
    include /etc/nginx/conf.d/*.conf;
}
```

### 3.2 跨境电商系统配置

```nginx
# /etc/nginx/conf.d/crosssell.conf

# 商城前端 - shop.example.com
server {
    listen 80;
    server_name shop.example.com;

    # SSL 配置（生产环境启用）
    # listen 443 ssl http2;
    # ssl_certificate /etc/nginx/ssl/shop.example.com.crt;
    # ssl_certificate_key /etc/nginx/ssl/shop.example.com.key;

    root /var/www/crosssell/public;
    index index.php;

    # API 路由
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 前台路由
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}

# 管理后台 - admin.example.com
server {
    listen 80;
    server_name admin.example.com;

    root /var/www/crosssell/public;
    index index.php;

    # 管理后台路由
    location /admin {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API 路由
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}

# 薪福通系统 - hr.example.com
server {
    listen 80;
    server_name hr.example.com;

    root /var/www/crosssell/public;
    index index.php;

    # 薪福通路由
    location /xinfutong {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API 路由
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}

# 权限管理 - super.example.com
server {
    listen 80;
    server_name super.example.com;

    root /var/www/crosssell/public;
    index index.php;

    # 权限管理路由
    location /super {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API 路由
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}
```

---

## 四、路由配置

### 4.1 API 路由

```php
// routes/api.php

use App\Http\Api\V1\CrossSell\OrderController;
use App\Http\Api\V1\CrossSell\ProductController;
use App\Http\Api\V1\CrossSell\TenantController;
use App\Http\Api\V1\CrossSell\WarehouseController;
use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {

    // 公开路由（无需认证）
    Route::post('/register', [AuthController::class, 'register'])
        ->name('api.register');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('api.login');

    // 需要 Admin JWT 认证的路由
    Route::middleware('auth:admin-api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('api.logout');

        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->name('api.refresh');

        Route::get('/me', [AuthController::class, 'me'])
            ->name('api.me');
    });

    // CrossSell API 路由 — 使用 CrossSell JWT 认证
    Route::middleware('auth:cross-sell')->prefix('v1/cross-sell')->group(function () {
        // 租户管理
        Route::apiResource('tenants', TenantController::class);

        // 商品管理
        Route::apiResource('products', ProductController::class);

        // 订单管理
        Route::apiResource('orders', OrderController::class);
        Route::post('orders/{id}/confirm', [OrderController::class, 'confirm']);
        Route::post('orders/{id}/ship', [OrderController::class, 'ship']);
        Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

        // 仓库管理
        Route::apiResource('warehouses', WarehouseController::class);
    });
});
```

### 4.2 Web 路由

```php
// routes/web.php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

// 数据导出 API（需要 session 认证，必须在 web 中间件组下）
Route::middleware('auth:admin')->prefix('admin/api/export')->group(function () {
    Route::post('/address', [ExportController::class, 'exportAddress'])
        ->name('admin.api.export.address');

    Route::get('/status', [ExportController::class, 'checkExportStatus'])
        ->name('admin.api.export.status');

    Route::get('/download/{filePath}', [ExportController::class, 'downloadExport'])
        ->where('filePath', '.*')
        ->name('admin.api.export.download');

    Route::post('/sync', [ExportController::class, 'syncExport'])
        ->name('admin.api.export.sync');
});

// 数据导入 API（需要 session 认证）
Route::middleware('auth:admin')->prefix('admin/api/import')->group(function () {
    Route::post('/product', [ImportController::class, 'importProducts'])
        ->name('admin.api.import.product');

    Route::post('/order', [ImportController::class, 'importOrders'])
        ->name('admin.api.import.order');

    Route::get('/product/template', [ImportController::class, 'downloadProductTemplate'])
        ->name('admin.api.import.product.template');

    Route::get('/order/template', [ImportController::class, 'downloadOrderTemplate'])
        ->name('admin.api.import.order.template');
});
```

---

## 五、Filament Panel 配置

### 5.1 商城前端 Panel（shop.example.com）

```php
// app/Providers/Filament/CustomerPanelProvider.php

<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CustomerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('customer')
            ->path('shop')
            ->domain('shop.example.com')
            ->authGuard('customer')
            ->login()
            ->registration()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Customer/Resources'), for: 'App\\Filament\\Customer\\Resources')
            ->discoverPages(in: app_path('Filament/Customer/Pages'), for: 'App\\Filament\\Customer\\Pages')
            ->discoverWidgets(in: app_path('Filament/Customer/Widgets'), for: 'App\\Filament\\Customer\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### 5.2 管理后台 Panel（admin.example.com）

```php
// app/Providers/Filament/AdminPanelProvider.php

<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\Login;
use App\Filament\Admin\Pages\ViewAddressList;
use App\Filament\Admin\Widgets\CrossSellStatsOverview;
use App\Filament\Admin\Widgets\LowStockAlert;
use App\Filament\Admin\Widgets\NotificationWidget;
use App\Filament\Admin\Widgets\OrderStatusChart;
use App\Filament\Admin\Widgets\OrderTrendChart;
use App\Filament\Admin\Widgets\RecentOrdersTable;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->domain('admin.example.com')
            ->authGuard('admin')
            ->login(Login::class)
            ->sidebarWidth('16rem')
            ->maxContentWidth('full')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
                ViewAddressList::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                NotificationWidget::class,
                CrossSellStatsOverview::class,
                OrderTrendChart::class,
                OrderStatusChart::class,
                RecentOrdersTable::class,
                LowStockAlert::class,
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### 5.3 薪福通 Panel（hr.example.com）

```php
// app/Providers/Filament/XinfutongPanelProvider.php

<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class XinfutongPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('xinfutong')
            ->path('xinfutong')
            ->domain('hr.example.com')
            ->authGuard('admin')
            ->login()
            ->sidebarWidth('16rem')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Xinfutong/Resources'), for: 'App\\Filament\\Xinfutong\\Resources')
            ->discoverPages(in: app_path('Filament/Xinfutong/Pages'), for: 'App\\Filament\\Xinfutong\\Pages')
            ->discoverWidgets(in: app_path('Filament/Xinfutong/Widgets'), for: 'App\\Filament\\Xinfutong\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### 5.4 权限管理 Panel（super.example.com）

```php
// app/Providers/Filament/PermissionPanelProvider.php

<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PermissionPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('permission')
            ->path('super')
            ->domain('super.example.com')
            ->authGuard('admin')
            ->login()
            ->sidebarWidth('16rem')
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Permission/Resources'), for: 'App\\Filament\\Permission\\Resources')
            ->discoverPages(in: app_path('Filament/Permission/Pages'), for: 'App\\Filament\\Permission\\Pages')
            ->discoverWidgets(in: app_path('Filament/Permission/Widgets'), for: 'App\\Filament\\Permission\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

---

## 六、数据库配置

### 6.1 独立数据库方案

```sql
-- 创建数据库
CREATE DATABASE crosssell_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE xinfutong_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE base_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE auth_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建用户
CREATE USER 'crosssell_user'@'%' IDENTIFIED BY 'password';
CREATE USER 'xinfutong_user'@'%' IDENTIFIED BY 'password';
CREATE USER 'base_user'@'%' IDENTIFIED BY 'password';
CREATE USER 'auth_user'@'%' IDENTIFIED BY 'password';

-- 授权
GRANT ALL PRIVILEGES ON crosssell_db.* TO 'crosssell_user'@'%';
GRANT ALL PRIVILEGES ON xinfutong_db.* TO 'xinfutong_user'@'%';
GRANT ALL PRIVILEGES ON base_db.* TO 'base_user'@'%';
GRANT ALL PRIVILEGES ON auth_db.* TO 'auth_user'@'%';

FLUSH PRIVILEGES;
```

### 6.2 .env 配置

```env
# 商城数据库
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crosssell_db
DB_USERNAME=crosssell_user
DB_PASSWORD=password

# 薪福通数据库
XINFUTONG_DB_CONNECTION=mysql
XINFUTONG_DB_HOST=127.0.0.1
XINFUTONG_DB_PORT=3306
XINFUTONG_DB_DATABASE=xinfutong_db
XINFUTONG_DB_USERNAME=xinfutong_user
XINFUTONG_DB_PASSWORD=password

# 基础数据库
BASE_DB_CONNECTION=mysql
BASE_DB_HOST=127.0.0.1
BASE_DB_PORT=3306
BASE_DB_DATABASE=base_db
BASE_DB_USERNAME=base_user
BASE_DB_PASSWORD=password

# 权限数据库
AUTH_DB_CONNECTION=mysql
AUTH_DB_HOST=127.0.0.1
AUTH_DB_PORT=3306
AUTH_DB_DATABASE=auth_db
AUTH_DB_USERNAME=auth_user
AUTH_DB_PASSWORD=password
```

---

## 七、部署脚本

### 7.1 Docker Compose 配置

```yaml
# docker-compose.prod.yml

version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: crosssell-app
    restart: always
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - crosssell-network
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    container_name: crosssell-nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    networks:
      - crosssell-network
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: crosssell-mysql
    restart: always
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: crosssell_db
      MYSQL_USER: crosssell_user
      MYSQL_PASSWORD: password
    volumes:
      - mysql-data:/var/lib/mysql
      - ./docker/mysql/init:/docker-entrypoint-initdb.d
    networks:
      - crosssell-network

  redis:
    image: redis:7-alpine
    container_name: crosssell-redis
    restart: always
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - crosssell-network

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: crosssell-phpmyadmin
    restart: always
    ports:
      - "8081:80"
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
    networks:
      - crosssell-network
    depends_on:
      - mysql

networks:
  crosssell-network:
    driver: bridge

volumes:
  mysql-data:
  redis-data:
```

### 7.2 部署脚本

```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 开始部署跨境电商系统..."

# 1. 拉取代码
echo "📦 拉取代码..."
git pull origin main

# 2. 安装依赖
echo "📦 安装依赖..."
composer install --optimize-autoloader --no-dev

# 3. 复制环境配置
echo "⚙️ 配置环境..."
cp .env.example .env
php artisan key:generate

# 4. 运行迁移
echo "🗄️ 运行迁移..."
php artisan migrate --force

# 5. 填充数据
echo "🌱 填充数据..."
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=BusinessDemoSeeder

# 6. 清除缓存
echo "🗑️ 清除缓存..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 7. 优化
echo "⚡ 优化..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. 设置权限
echo "🔐 设置权限..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 9. 重启服务
echo "🔄 重启服务..."
docker compose restart

echo "✅ 部署完成！"
echo ""
echo "📋 访问地址："
echo "   商城前端: http://shop.example.com"
echo "   管理后台: http://admin.example.com"
echo "   薪福通: http://hr.example.com"
echo "   权限管理: http://super.example.com"
```

---

## 八、监控和日志

### 8.1 日志配置

```php
// config/logging.php

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
    ],

    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],

    // 按系统分日志
    'crosssell' => [
        'driver' => 'daily',
        'path' => storage_path('logs/crosssell.log'),
        'level' => 'debug',
        'days' => 30,
    ],

    'xinfutong' => [
        'driver' => 'daily',
        'path' => storage_path('logs/xinfutong.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],
```

### 8.2 健康检查

```php
// routes/api.php

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'services' => [
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
            'cache' => Cache::store()->getStore() ? 'connected' : 'disconnected',
        ],
    ]);
});
```

---

## 九、安全配置

### 9.1 SSL 证书

```bash
# 使用 Let's Encrypt 获取免费 SSL 证书
certbot --nginx -d shop.example.com -d admin.example.com -d hr.example.com -d super.example.com

# 自动续期
certbot renew --quiet
```

### 9.2 防火墙配置

```bash
# UFW 防火墙
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp
ufw enable
```

---

## 十、备份策略

### 10.1 数据库备份

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/mysql"

# 备份所有数据库
mysqldump -u root -prootpassword --all-databases > $BACKUP_DIR/all_databases_$DATE.sql

# 压缩
gzip $BACKUP_DIR/all_databases_$DATE.sql

# 保留最近 30 天
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "✅ 备份完成: $BACKUP_DIR/all_databases_$DATE.sql.gz"
```

### 10.2 定时备份

```bash
# crontab -e
# 每天凌晨 2 点备份
0 2 * * * /path/to/backup.sh >> /var/log/backup.log 2>&1
```

---

## 十一、性能优化

### 11.1 PHP-FPM 配置

```ini
; /etc/php/8.2/fpm/pool.d/www.conf

[www]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35

pm.max_requests = 500

php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
```

### 11.2 Redis 配置

```conf
# /etc/redis/redis.conf

maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

---

## 十二、监控告警

### 12.1 监控脚本

```bash
#!/bin/bash
# monitor.sh

# 检查服务状态
check_service() {
    local service=$1
    local url=$2
    
    response=$(curl -s -o /dev/null -w "%{http_code}" $url)
    
    if [ $response -ne 200 ]; then
        echo "❌ $service 服务异常 (HTTP $response)"
        # 发送告警通知
        curl -X POST https://hooks.slack.com/services/xxx/yyy/zzz \
            -d '{"text":"⚠️ '$service' 服务异常"}'
    else
        echo "✅ $service 服务正常"
    fi
}

# 检查各个服务
check_service "商城前端" "http://shop.example.com/health"
check_service "管理后台" "http://admin.example.com/health"
check_service "薪福通" "http://hr.example.com/health"
check_service "权限管理" "http://super.example.com/health"
```

### 12.2 定时监控

```bash
# crontab -e
# 每 5 分钟检查一次
*/5 * * * * /path/to/monitor.sh >> /var/log/monitor.log 2>&1
```
