#!/bin/bash
# ============================================
# Database Snapshot Script (Backup + Seed)
# Creates a clean snapshot with seeded data
# Usage: ./docker/mysql/snapshot.sh [snapshot_name]
# ============================================

set -e

# 配置
SNAPSHOT_DIR="./backups/snapshots"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
SNAPSHOT_NAME="${1:-snapshot_${TIMESTAMP}}"

# 创建快照目录
mkdir -p "${SNAPSHOT_DIR}"

echo "=========================================="
echo "  Database Snapshot"
echo "=========================================="
echo "Name: ${SNAPSHOT_NAME}"
echo "=========================================="
echo ""

# Step 1: 备份当前数据库
echo "[1/4] 正在备份当前数据库..."
./docker/mysql/backup.sh "snapshot_${SNAPSHOT_NAME}"

# Step 2: 运行迁移和 Seed
echo ""
echo "[2/4] 正在重置数据库..."
docker exec "filament-app" php artisan migrate:fresh --seed --force

# Step 3: 备份 seed 后的数据
echo ""
echo "[3/4] 正在备份 seed 数据..."
./docker/mysql/backup.sh "seeded_${SNAPSHOT_NAME}"

# Step 4: 记录快照信息
echo ""
echo "[4/4] 正在保存快照信息..."
cat > "${SNAPSHOT_DIR}/${SNAPSHOT_NAME}.info" << EOF
Snapshot: ${SNAPSHOT_NAME}
Created:  $(date '+%Y-%m-%d %H:%M:%S')
Files:
  - seeded_${SNAPSHOT_NAME}.sql.gz (seeded data)
  - snapshot_${SNAPSHOT_NAME}.sql.gz (before seed)
EOF

echo ""
echo "=========================================="
echo "  ✅ 快照创建完成！"
echo "=========================================="
echo "快照目录: ${SNAPSHOT_DIR}"
echo "恢复命令: ./docker/mysql/restore.sh seeded_${SNAPSHOT_NAME}"
echo "=========================================="
