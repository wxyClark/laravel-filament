#!/usr/bin/env bash

###############################################################################
# Laravel-Filament 质量工具链一键安装脚本
# 自动安装和配置所有代码质量保障工具
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_step() { echo -e "${BLUE}▶ $1${NC}"; }
print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${YELLOW}ℹ $1${NC}"; }

main() {
    echo -e "\n${BLUE}============================================${NC}"
    echo -e "${BLUE}  Laravel-Filament 质量工具链安装${NC}"
    echo -e "${BLUE}============================================${NC}\n"

    # Step 1: PHPStan + Larastan
    print_step "安装 PHPStan + Larastan..."
    if ! composer show phpstan/phpstan 2>/dev/null; then
        composer require --dev phpstan/phpstan larastan/larastan:^2.9 --no-interaction
    fi
    print_success "PHPStan 已安装"

    # Step 2: 配置 PHPStan
    print_step "配置 PHPStan..."
    if [ ! -f phpstan.neon ]; then
        cp .ai/configs/phpstan.neon phpstan.neon 2>/dev/null || true
        print_info "phpstan.neon 已创建（如使用默认配置请手动调整）"
    fi
    print_success "PHPStan 配置完成"

    # Step 3: Pint 配置
    print_step "配置 Pint 代码风格..."
    if [ ! -f pint.json ]; then
        cp .ai/configs/pint.json pint.json 2>/dev/null || true
        print_info "pint.json 已创建"
    fi
    print_success "Pint 配置完成"

    # Step 4: PHP CS Fixer（可选，与 Pint 二选一）
    print_info "如需 PHP CS Fixer（与 Pint 二选一）："
    print_info "  composer require --dev friendsofphp/php-cs-fixer"
    print_info "  cp .ai/configs/.php-cs-fixer.php .php-cs-fixer.php"

    # Step 5: Git Hooks
    print_step "安装 Git Hooks..."
    if [ -d ".git/hooks" ]; then
        cp .git/hooks/pre-commit.sample .git/hooks/pre-commit 2>/dev/null || true
        if [ -f scripts/check-code-quality.sh ]; then
            cp scripts/check-code-quality.sh .git/hooks/pre-commit 2>/dev/null || true
            chmod +x .git/hooks/pre-commit
            chmod +x scripts/check-code-quality.sh
            print_success "Git Hooks 已安装"
        else
            print_info "请先放置 scripts/check-code-quality.sh 再运行此脚本"
        fi
    else
        print_error "未找到 .git 目录，请在 Git 仓库中运行"
    fi

    # Step 6: VSCode 配置
    print_step "配置 VSCode..."
    mkdir -p .vscode
    print_success "VSCode 目录已创建"
    print_info "请安装以下 VSCode 扩展："
    print_info "  - Laravel Blade Snippets"
    print_info "  - PHP Intelephense"
    print_info "  - PHP CS Fixer"
    print_info "  - Laravel Extra Intellisense"

    # Step 7: 运行一次完整检查
    print_step "运行首次完整检查..."
    print_info "运行: ./vendor/bin/pint --test"
    if [ -f vendor/bin/pint ]; then
        if ./vendor/bin/pint --test --path=app 2>/dev/null; then
            print_success "Pint 检查通过"
        else
            print_info "运行 ./vendor/bin/pint 自动修复"
        fi
    fi

    echo -e "\n${GREEN}============================================${NC}"
    echo -e "${GREEN}  安装完成！${NC}"
    echo -e "${GREEN}============================================${NC}\n"

    print_info "下一步操作："
    echo "  1. 配置 .env 文件"
    echo "  2. 运行: php artisan key:generate"
    echo "  3. 运行: php artisan migrate"
    echo "  4. 运行: ./vendor/bin/phpstan analyse"
    echo "  5. 运行: ./vendor/bin/pest"
    echo ""
}

main "$@"
