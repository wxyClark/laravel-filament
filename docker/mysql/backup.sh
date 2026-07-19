#!/bin/bash
# ============================================
# MySQL Database Backup Script
# Usage: ./docker/mysql/backup.sh [backup_name]
# ============================================

set -e

# 配置
BACKUP_DIR="./backups/mysql"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="${1:-backup_${TIMESTAMP}}"
BACKUP_PATH="${BACKUP_DIR}/${BACKUP_NAME}.sql"

# 从 docker-compose.yml 读取数据库配置
DB_CONTAINER="filament-mysql"
DB_NAME="${DB_DATABASE:-laravel}"
DB_USER="${DB_USERNAME:-laravel}"
DB_PASSWORD="${DB_PASSWORD:-secret}"
DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-rootsecret}"

# 创建备份目录
mkdir -p "${BACKUP_DIR}"

echo "=========================================="
echo "  MySQL Database Backup"
echo "=========================================="
echo "Database: ${DB_NAME}"
echo "Backup:   ${BACKUP_PATH}"
echo "=========================================="

# 执行备份
echo "[1/2] 正在备份数据库..."
docker exec "${DB_CONTAINER}" mysqldump \
    -u root \
    -p"${DB_ROOT_PASSWORD}" \
    "${DB_NAME}" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    > "${BACKUP_PATH}"

# 压缩备份
echo "[2/2] 正在压缩备份..."
gzip "${BACKUP_PATH}"

BACKUP_GZ="${BACKUP_PATH}.gz"
BACKUP_SIZE=$(du -h "${BACKUP_GZ}" | cut -f1)

echo "=========================================="
echo "  ✅ 备份完成！"
echo "=========================================="
echo "文件: ${BACKUP_GZ}"
echo "大小: ${BACKUP_SIZE}"
echo "=========================================="
