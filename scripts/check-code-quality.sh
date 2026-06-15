#!/usr/bin/env bash

###############################################################################
# Laravel-Filament 代码质量自动检查脚本
# 在 pre-commit 钩子中自动执行
###############################################################################

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 打印函数
info() { echo -e "${BLUE}ℹ $1${NC}"; }
success() { echo -e "${GREEN}✓ $1${NC}"; }
warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
error() { echo -e "${RED}✗ $1${NC}"; }

# 检查是否只有 staging 文件
get_staged_files() {
    git diff --cached --name-only --diff-filter=ACM | grep -E '\.php$' || true
}

# 获取所有 PHP 文件（用于全量检查）
get_all_php_files() {
    find app database tests -name '*.php' 2>/dev/null || true
}

main() {
    info "🔍 Laravel-Filament 代码质量检查开始..."
    echo ""

    # 检查 1: PHP 语法
    info "📝 [1/5] 检查 PHP 语法..."
    STAGED_FILES=$(get_staged_files)
    if [ -z "$STAGED_FILES" ]; then
        info "  没有 PHP 文件被修改，跳过语法检查"
    else
        SYNTAX_ERRORS=0
        while IFS= read -r file; do
            if [ -f "$file" ]; then
                if ! php -l "$file" > /dev/null 2>&1; then
                    error "  语法错误: $file"
                    php -l "$file"
                    SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
                fi
            fi
        done <<< "$STAGED_FILES"

        if [ $SYNTAX_ERRORS -gt 0 ]; then
            error "  发现 $SYNTAX_ERRORS 个语法错误"
            exit 1
        fi
        success "  PHP 语法检查通过"
    fi
    echo ""

    # 检查 2: Pint 代码格式化
    info "🎨 [2/5] 运行 Pint 代码格式化检查..."
    if [ -z "$STAGED_FILES" ]; then
        # 没有暂存文件时检查所有 PHP 文件
        if command -v ./vendor/bin/pint &> /dev/null; then
            if ! ./vendor/bin/pint --test 2>/dev/null; then
                warning "  代码格式不符合规范，运行 './vendor/bin/pint' 自动修复"
                warning "  修复后重新提交"
                exit 1
            fi
            success "  Pint 检查通过"
        fi
    else
        if command -v ./vendor/bin/pint &> /dev/null; then
            if ! ./vendor/bin/pint --test --dirty 2>/dev/null; then
                warning "  代码格式不符合规范，运行 './vendor/bin/pint' 自动修复"
                warning "  修复后重新提交"
                exit 1
            fi
            success "  Pint 检查通过"
        fi
    fi
    echo ""

    # 检查 3: Filament 资源规范检查（自定义）
    info "📦 [3/5] 检查 Filament 资源规范..."
    RESOURCE_ERRORS=0
    for file in $STAGED_FILES; do
        if [[ "$file" == *"Resource.php" ]] && [[ "$file" == *"Filament"* ]]; then
            # 检查 Resource 是否定义了 form() 方法
            if ! grep -q "public static function form" "$file" 2>/dev/null; then
                error "  $file 缺少 form() 方法定义"
                RESOURCE_ERRORS=$((RESOURCE_ERRORS + 1))
            fi
            # 检查 Resource 是否定义了 table() 方法
            if ! grep -q "public static function table" "$file" 2>/dev/null; then
                error "  $file 缺少 table() 方法定义"
                RESOURCE_ERRORS=$((RESOURCE_ERRORS + 1))
            fi
        fi
        # 检查 Resource 中是否包含业务逻辑（Service 调用以外的）
        if [[ "$file" == *"Resource.php" ]] && grep -q "DB::transaction" "$file" 2>/dev/null; then
            warning "  警告: $file 包含 DB::transaction() 调用，业务逻辑应移至 Service 层"
        fi
        if [[ "$file" == *"Resource.php" ]] && grep -q "mail(" "$file" 2>/dev/null; then
            warning "  警告: $file 包含 mail() 调用，邮件逻辑应移至 Service 层"
        fi
    done

    if [ $RESOURCE_ERRORS -gt 0 ]; then
        error "  发现 $RESOURCE_ERRORS 个 Filament 规范错误"
        exit 1
    fi
    success "  Filament 资源规范检查通过"
    echo ""

    # 检查 4: PHPStan 静态分析（仅检查暂存文件）
    info "🔬 [4/5] 运行 PHPStan 静态分析..."
    if [ -n "$STAGED_FILES" ] && command -v ./vendor/bin/phpstan &> /dev/null; then
        # 构建文件列表参数
        FILE_ARGS=""
        while IFS= read -r file; do
            if [ -f "$file" ]; then
                FILE_ARGS="$FILE_ARGS $file"
            fi
        done <<< "$STAGED_FILES"

        if [ -n "$FILE_ARGS" ]; then
            if ! ./vendor/bin/phpstan analyse --error-format=raw $FILE_ARGS 2>/dev/null | head -20; then
                warning "  PHPStan 发现类型问题（使用 --no-progress 查看详细信息）"
            fi
        fi
    else
        info "  跳过 PHPStan 检查（未安装或无暂存文件）"
    fi
    echo ""

    # 检查 5: 文件命名规范检查
    info "📋 [5/5] 检查文件命名规范..."
    NAMING_ERRORS=0

    # 检查 Resource 文件命名
    for file in $STAGED_FILES; do
        if [[ "$file" == *"Resource.php" ]]; then
            basename_file=$(basename "$file" .php)
            if [[ "$basename_file" != *"Resource" ]]; then
                error "  $file 不符合命名规范，应为 [Entity]Resource.php"
                NAMING_ERRORS=$((NAMING_ERRORS + 1))
            fi
        fi
        # 检查 Page 文件命名
        if [[ "$file" == *"Pages/"* ]]; then
            basename_file=$(basename "$file" .php)
            valid_prefixes=("List" "Create" "Edit" "View")
            is_valid=false
            for prefix in "${valid_prefixes[@]}"; do
                if [[ "$basename_file" == ${prefix}* ]]; then
                    is_valid=true
                    break
                fi
            done
            if [ "$is_valid" = false ]; then
                warning "  $file 页面命名可能不符合规范（应为 List/Create/Edit/View 开头）"
            fi
        fi
    done

    if [ $NAMING_ERRORS -gt 0 ]; then
        error "  发现 $NAMING_ERRORS 个命名规范错误"
        exit 1
    fi
    success "  文件命名规范检查通过"
    echo ""

    success "🎉 所有检查通过！可以提交代码"
    exit 0
}

main "$@"
