#!/bin/bash
# ============================================
# MySQL Database Restore Script
# Usage: ./docker/mysql/restore.sh <backup_file>
# ============================================

set -e

# 配置
BACKUP_DIR="./backups/mysql"
DB_CONTAINER="filament-mysql"
DB_NAME="${DB_DATABASE:-laravel}"
DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-rootsecret}"

# 检查参数
if [ -z "$1" ]; then
    echo "=========================================="
    echo "  可用的备份文件："
    echo "=========================================="
    ls -lh "${BACKUP_DIR}"/*.sql.gz 2>/dev/null || echo "  没有找到备份文件"
    echo ""
    echo "  用法: $0 <backup_name>"
    echo "  示例: $0 backup_20260719_120000"
    echo "=========================================="
    exit 1
fi

BACKUP_NAME="$1"
BACKUP_FILE="${BACKUP_DIR}/${BACKUP_NAME}.sql.gz"

# 检查备份文件是否存在
if [ ! -f "${BACKUP_FILE}" ]; then
    echo "❌ 错误: 备份文件不存在: ${BACKUP_FILE}"
    echo ""
    echo "可用的备份文件："
    ls -lh "${BACKUP_DIR}"/*.sql.gz 2>/dev/null || echo "  没有找到备份文件"
    exit 1
fi

BACKUP_SIZE=$(du -h "${BACKUP_FILE}" | cut -f1)

echo "=========================================="
echo "  MySQL Database Restore"
echo "=========================================="
echo "Database:  ${DB_NAME}"
echo "Backup:    ${BACKUP_FILE}"
echo "Size:      ${BACKUP_SIZE}"
echo "=========================================="
echo ""
echo "⚠️  警告: 此操作将覆盖当前数据库！"
echo ""
read -p "确认恢复? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "已取消操作"
    exit 0
fi

echo "[1/3] 正在解压备份..."
gunzip -c "${BACKUP_FILE}" | docker exec -i "${DB_CONTAINER}" \
    mysql -u root -p"${DB_ROOT_PASSWORD}" "${DB_NAME}"

echo "[2/3] 正在清除缓存..."
docker exec "filament-app" php artisan config:clear 2>/dev/null || true
docker exec "filament-app" php artisan cache:clear 2>/dev/null || true
docker exec "filament-app" php artisan route:clear 2>/dev/null || true
docker exec "filament-app" php artisan view:clear 2>/dev/null || true

echo "[3/3] 正在重建缓存..."
docker exec "filament-app" php artisan config:cache 2>/dev/null || true
docker exec "filament-app" php artisan route:cache 2>/dev/null || true

echo ""
echo "=========================================="
echo "  ✅ 恢复完成！"
echo "=========================================="
echo "数据库已恢复到: ${BACKUP_NAME}"
echo "=========================================="
