# ============================================
# Optimized PHP Dockerfile for Laravel + Filament
# Multi-stage build with Alpine Linux
# ============================================

# Stage 1: Build dependencies
FROM php:8.5-fpm-alpine AS builder

# 配置清华镜像源加速
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.tuna.tsinghua.edu.cn/g' /etc/apk/repositories

# 安装构建依赖
RUN apk add --no-cache \
    git \
    curl \
    autoconf \
    g++ \
    make \
    pkgconf \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && pecl install redis \
    && docker-php-ext-enable redis

# Stage 2: Production image
FROM php:8.5-fpm-alpine AS production

# 配置清华镜像源加速
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.tuna.tsinghua.edu.cn/g' /etc/apk/repositories

# 安装运行时依赖（最小化）+ curl for healthcheck
RUN apk add --no-cache \
    libpng \
    libxml2 \
    libzip \
    oniguruma \
    icu-libs \
    curl

# 复制PHP扩展从builder阶段
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# 创建必要的目录并设置权限
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 复制启动脚本
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 设置工作目录
WORKDIR /var/www/html

# 暴露端口
EXPOSE 9000

# 启动命令（修复权限后启动 FPM）
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
