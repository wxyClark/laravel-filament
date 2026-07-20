#!/usr/bin/env bash

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

success_count=0
error_count=0

print_status() {
    local status=$1
    local message=$2
    if [ "$status" = "ok" ]; then
        echo -e "${GREEN}[OK]${NC} $message"
        success_count=$((success_count + 1))
    elif [ "$status" = "warn" ]; then
        echo -e "${YELLOW}[WARN]${NC} $message"
    else
        echo -e "${RED}[ERROR]${NC} $message"
        error_count=$((error_count + 1))
    fi
}

echo "=============================================="
echo "     OpenCode 启动前验证脚本"
echo "=============================================="
echo

echo "[1/5] 检查 opencode 安装和 PATH..."
if command -v opencode &> /dev/null; then
    VERSION=$(opencode --version)
    print_status "ok" "opencode 版本: $VERSION"
else
    if [ -f "$HOME/.opencode/bin/opencode" ]; then
        print_status "warn" "opencode 已安装但不在 PATH 中"
        print_status "warn" "请运行: source ~/.zshrc 或重启终端"
    else
        print_status "error" "opencode 未安装"
    fi
fi

echo
echo "[2/5] 验证 opencode.json 配置..."
if [ -f "opencode.json" ]; then
    if command -v opencode &> /dev/null; then
        if opencode debug config &> /dev/null; then
            print_status "ok" "opencode.json 配置有效"
            INSTRUCTIONS=$(opencode debug config 2>&1 | grep -o '"instructions": \[[^]]*\]' | grep -o '"[^"]*"' | wc -l)
            print_status "ok" "已配置 $INSTRUCTIONS 个指令文件"
        else
            print_status "error" "opencode.json 配置无效，请检查格式"
            opencode debug config 2>&1 | head -20
        fi
    else
        print_status "warn" "跳过配置验证（opencode 不在 PATH）"
    fi
else
    print_status "error" "opencode.json 不存在"
fi

echo
echo "[3/5] 检查技能文件..."
SKILL_DIR=".ai/skills"
if [ -d "$SKILL_DIR" ]; then
    TOTAL_SKILLS=$(ls -la "$SKILL_DIR" | grep "^d" | grep -v "\.\." | wc -l)
    MISSING_SKILL_FILES=0
    for skill_dir in "$SKILL_DIR"/*/; do
        skill_name=$(basename "$skill_dir")
        if [ ! -f "$skill_dir/SKILL.md" ]; then
            print_status "error" "$skill_name 缺少 SKILL.md 文件"
            MISSING_SKILL_FILES=$((MISSING_SKILL_FILES + 1))
        fi
    done
    if [ $MISSING_SKILL_FILES -eq 0 ]; then
        print_status "ok" "全部 $TOTAL_SKILLS 个技能目录都有 SKILL.md 文件"
    fi
    
    if command -v opencode &> /dev/null; then
        LOADED_SKILLS=$(opencode debug skill 2>&1 | grep '"name":' | wc -l)
        print_status "ok" "opencode 已加载 $LOADED_SKILLS 个技能"
    fi
else
    print_status "error" "技能目录 $SKILL_DIR 不存在"
fi

echo
echo "[4/5] 检查锁文件..."
LOCK_DIR="$HOME/.local/state/opencode/locks"
if [ -d "$LOCK_DIR" ]; then
    LOCK_FILES=$(ls "$LOCK_DIR" 2>/dev/null | wc -l)
    if [ $LOCK_FILES -gt 0 ]; then
        print_status "warn" "发现 $LOCK_FILES 个锁文件，可能影响启动"
        print_status "warn" "可以删除: rm -f $LOCK_DIR/*"
    else
        print_status "ok" "没有锁文件"
    fi
else
    print_status "ok" "锁目录不存在（正常）"
fi

echo
echo "[5/5] 检查运行进程..."
RUNNING_PROCS=$(ps aux | grep opencode | grep -v grep | wc -l)
if [ $RUNNING_PROCS -gt 0 ]; then
    print_status "warn" "发现 $RUNNING_PROCS 个正在运行的 opencode 进程"
    ps aux | grep opencode | grep -v grep
else
    print_status "ok" "没有正在运行的 opencode 进程"
fi

echo
echo "=============================================="
if [ $error_count -eq 0 ]; then
    echo -e "${GREEN}✓ 所有检查通过，可以启动 opencode${NC}"
    echo "运行: opencode"
else
    echo -e "${RED}✗ 发现 $error_count 个错误，请修复后再启动${NC}"
fi
echo "=============================================="

exit $error_count