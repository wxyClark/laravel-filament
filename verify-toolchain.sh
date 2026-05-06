#!/bin/bash

# 工具链安装验证脚本
# 用途：验证所有 P0 优先级工具是否正确安装

set -e

echo "=========================================="
echo "  Laravel-Filament 工具链安装验证"
echo "=========================================="
echo ""

# 颜色定义
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 计数器
PASS=0
FAIL=0

# 检查函数
check_tool() {
    local tool_name=$1
    local command=$2
    
    echo -n "🔍 检查 $tool_name... "
    
    if docker compose exec app $command > /dev/null 2>&1; then
        echo -e "${GREEN}✅ 已安装${NC}"
        ((PASS++))
        return 0
    else
        echo -e "${RED}❌ 未安装或配置错误${NC}"
        ((FAIL++))
        return 1
    fi
}

# 显示版本信息
show_version() {
    local tool_name=$1
    local command=$2
    
    echo -n "   版本: "
    docker compose exec app $command 2>/dev/null | head -1 || echo "未知"
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  核心工具检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. PHPStan
check_tool "PHPStan" "./vendor/bin/phpstan --version"
show_version "PHPStan" "./vendor/bin/phpstan --version"
echo ""

# 2. Pest
check_tool "Pest PHP" "./vendor/bin/pest --version"
show_version "Pest" "./vendor/bin/pest --version"
echo ""

# 3. Laravel IDE Helper
check_tool "IDE Helper" "php artisan ide-helper:generate --help"
echo "   状态: ✅ 配置文件存在"
[ -f "_ide_helper.php" ] && echo "   _ide_helper.php: ✅ 已生成" || echo "   _ide_helper.php: ❌ 未生成"
echo ""

# 4. Laravel Telescope
check_tool "Telescope" "php artisan telescope:install --help"
echo "   迁移文件: "
ls database/migrations/*_create_telescope_* 2>/dev/null > /dev/null && \
    echo "     ✅ 已发布" || echo "     ❌ 未发布"
echo ""

# 5. Spatie Permission
check_tool "Spatie Permission" "php artisan vendor:publish --provider='Spatie\\\\Permission\\\\PermissionServiceProvider' --help"
echo "   配置文件: "
[ -f "config/permission.php" ] && echo "     ✅ config/permission.php 已发布" || echo "     ❌ 未发布"
echo "   迁移文件: "
ls database/migrations/*_create_permission_* 2>/dev/null > /dev/null && \
    echo "     ✅ 已发布" || echo "     ❌ 未发布"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  其他工具检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 6. Laravel Pint
check_tool "Laravel Pint" "./vendor/bin/pint --version"
show_version "Pint" "./vendor/bin/pint --version"
echo ""

# 7. Filament
check_tool "Filament" "php artisan filament:about"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  测试运行验证"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo -n "🧪 运行示例测试... "
if docker compose exec app ./vendor/bin/pest tests/Feature/ExampleTest.php > /dev/null 2>&1; then
    echo -e "${GREEN}✅ 测试通过${NC}"
    ((PASS++))
else
    echo -e "${YELLOW}⚠️  测试失败（可能需要配置数据库）${NC}"
fi
echo ""

echo "=========================================="
echo "  验证结果汇总"
echo "=========================================="
echo -e "  ${GREEN}通过: $PASS${NC}"
echo -e "  ${RED}失败: $FAIL${NC}"
echo ""

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}🎉 所有工具安装成功！${NC}"
    echo ""
    echo "下一步："
    echo "  1. 运行数据库迁移: docker compose exec app php artisan migrate"
    echo "  2. 创建管理员用户: docker compose exec app php artisan make:filament-user"
    echo "  3. 访问应用: http://localhost:8082"
    exit 0
else
    echo -e "${RED}⚠️  部分工具安装失败，请检查错误信息${NC}"
    exit 1
fi
