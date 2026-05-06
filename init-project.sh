#!/bin/bash

###############################################################################
# Laravel-Filament 项目初始化工具
# 
# 用途：快速安装核心工具包并配置基础环境
# 执行时间：约 5-10 分钟
###############################################################################

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 打印函数
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_step() {
    echo -e "${YELLOW}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# 检查 Docker 是否运行
check_docker() {
    if ! docker compose version &> /dev/null; then
        print_error "Docker Compose 未安装或未运行"
        exit 1
    fi
    print_success "Docker Compose 已就绪"
}

# 主流程
main() {
    print_header "Laravel-Filament 项目初始化"
    
    cd /home/clark/www/laravel-filament
    
    # 步骤 0：检查 Docker
    print_step "检查 Docker 环境..."
    check_docker
    
    # 步骤 1：启动容器
    print_header "步骤 1: 启动 Docker 容器"
    print_step "启动应用容器..."
    docker compose up -d
    
    print_step "等待容器就绪..."
    sleep 5
    
    # 步骤 2：安装依赖
    print_header "步骤 2: 安装 Composer 依赖"
    print_step "安装基础依赖..."
    docker compose exec app composer install --no-interaction
    print_success "基础依赖安装完成"
    
    # 步骤 3：生成应用密钥
    print_header "步骤 3: 生成应用密钥"
    if [ ! -f .env ] || ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
        print_step "生成 APP_KEY..."
        docker compose exec app php artisan key:generate
        print_success "APP_KEY 已生成"
    else
        print_success "APP_KEY 已存在"
    fi
    
    # 步骤 4：安装 Filament
    print_header "步骤 4: 安装 Filament 后台"
    print_step "运行 Filament 安装命令..."
    docker compose exec app php artisan filament:install --panels --no-interaction
    print_success "Filament 安装完成"
    
    # 步骤 5：安装核心工具包
    print_header "步骤 5: 安装核心开发工具"
    
    # PHPStan
    print_step "安装 PHPStan + Larastan（静态分析）..."
    docker compose exec app composer require --dev phpstan/phpstan larastan/larastan:^2.0 --no-interaction
    print_success "PHPStan 安装完成"
    
    # Pest
    print_step "安装 Pest PHP（测试框架）..."
    docker compose exec app composer require --dev pestphp/pest:^2.0 pestphp/pest-plugin-laravel:^2.0 --no-interaction
    docker compose exec app ./vendor/bin/pest --init
    print_success "Pest 安装完成"
    
    # IDE Helper
    print_step "安装 Laravel IDE Helper..."
    docker compose exec app composer require --dev barryvdh/laravel-ide-helper --no-interaction
    print_success "IDE Helper 安装完成"
    
    # Telescope
    print_step "安装 Laravel Telescope（调试面板）..."
    docker compose exec app composer require --dev laravel/telescope --no-interaction
    docker compose exec app php artisan telescope:install
    docker compose exec app php artisan migrate
    print_success "Telescope 安装完成"
    
    # Spatie Permission
    print_step "安装 Spatie Laravel Permission（RBAC）..."
    docker compose exec app composer require spatie/laravel-permission --no-interaction
    docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    docker compose exec app php artisan migrate
    print_success "Spatie Permission 安装完成"
    
    # 步骤 6：创建目录结构
    print_header "步骤 6: 创建 DDD 目录结构"
    
    print_step "创建领域层目录..."
    docker compose exec app mkdir -p app/Domains/{User,Product,Trade,O2O,Distribution,CRM,DRP,Finance}/{Models,Services,Repositories,Events}
    
    print_step "创建 Filament 资源目录..."
    docker compose exec app mkdir -p app/Filament/{Resources,Widgets,Pages}
    
    print_step "创建测试目录..."
    docker compose exec app mkdir -p tests/{Unit,Feature,Integration}
    
    print_step "创建数据库目录..."
    docker compose exec app mkdir -p database/{migrations,seeders,factories}
    
    print_success "目录结构创建完成"
    
    # 步骤 7：生成 IDE Helper 文件
    print_header "步骤 7: 生成 IDE Helper 文件"
    print_step "生成 Facade 提示..."
    docker compose exec app php artisan ide-helper:generate
    print_step "生成 Meta 文件..."
    docker compose exec app php artisan ide-helper:meta
    print_success "IDE Helper 文件生成完成"
    
    # 步骤 8：创建管理员用户
    print_header "步骤 8: 创建 Filament 管理员用户"
    print_step "请手动运行以下命令创建管理员："
    echo -e "${YELLOW}  docker compose exec app php artisan make:filament-user${NC}"
    echo ""
    
    # 步骤 9：运行 PHPStan
    print_header "步骤 9: 运行静态分析"
    print_step "初始化 PHPStan 配置..."
    docker compose exec app ./vendor/bin/phpstan analyse --generate-baseline
    print_success "PHPStan 基线生成完成"
    
    # 步骤 10：运行测试
    print_header "步骤 10: 运行测试套件"
    print_step "运行 Pest 测试..."
    docker compose exec app ./vendor/bin/pest
    print_success "测试运行完成"
    
    # 完成
    print_header "🎉 初始化完成！"
    
    echo -e "${GREEN}✅ 已完成的工作：${NC}"
    echo "  1. ✓ Filament 后台已安装"
    echo "  2. ✓ 核心工具包已安装（PHPStan, Pest, IDE Helper, Telescope）"
    echo "  3. ✓ RBAC 权限系统已配置"
    echo "  4. ✓ DDD 目录结构已创建"
    echo "  5. ✓ IDE Helper 文件已生成"
    echo ""
    
    echo -e "${YELLOW}⚠️  下一步操作：${NC}"
    echo "  1. 创建管理员用户："
    echo -e "     ${BLUE}docker compose exec app php artisan make:filament-user${NC}"
    echo ""
    echo "  2. 访问 Filament 后台："
    echo -e "     ${BLUE}http://localhost:8082/admin${NC}"
    echo ""
    echo "  3. 访问 Telescope 调试面板："
    echo -e "     ${BLUE}http://localhost:8082/telescope${NC}"
    echo ""
    echo "  4. 查看审计报告："
    echo -e "     ${BLUE}cat doc/AUDIT_REPORT_2026-05-01.md${NC}"
    echo ""
    
    echo -e "${GREEN}📚 相关文档：${NC}"
    echo "  - 审计报告: doc/AUDIT_REPORT_2026-05-01.md"
    echo "  - 架构规范: doc/design/01-architecture-spec.md"
    echo "  - PRD 索引: doc/PRD/00-PRD-INDEX.md"
    echo ""
}

# 执行主流程
main "$@"
