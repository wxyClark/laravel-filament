#!/bin/bash

# ============================================
# Laravel Docker 环境管理脚本
# ============================================

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 脚本目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# 函数定义
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 显示帮助信息
show_help() {
    echo "========================================"
    echo "Laravel Docker 环境管理脚本"
    echo "========================================"
    echo ""
    echo "用法: ./docker.sh [命令]"
    echo ""
    echo "可用命令:"
    echo "  start       启动所有服务"
    echo "  stop        停止所有服务"
    echo "  restart     重启所有服务"
    echo "  logs        查看日志"
    echo "  status      查看服务状态"
    echo "  build       构建/重新构建镜像"
    echo "  clean       清理 Docker 资源"
    echo "  shell       进入 App 容器"
    echo "  artisan     运行 Artisan 命令"
    echo "  composer    运行 Composer 命令"
    echo "  help        显示帮助信息"
    echo ""
}

# 启动服务
start_services() {
    log_info "启动 Laravel Docker 环境..."
    cd "$SCRIPT_DIR"
    docker compose up -d
    log_success "服务已启动！"
    show_status
}

# 停止服务
stop_services() {
    log_info "停止 Laravel Docker 环境..."
    cd "$SCRIPT_DIR"
    docker compose down
    log_success "服务已停止！"
}

# 重启服务
restart_services() {
    log_info "重启 Laravel Docker 环境..."
    cd "$SCRIPT_DIR"
    docker compose restart
    log_success "服务已重启！"
    show_status
}

# 查看日志
show_logs() {
    cd "$SCRIPT_DIR"
    docker compose logs -f --tail=100
}

# 查看状态
show_status() {
    echo ""
    echo "========================================"
    echo "服务状态"
    echo "========================================"
    cd "$SCRIPT_DIR"
    docker compose ps
    echo ""
    echo -e "${GREEN}访问地址：${NC}"
    echo "  应用: http://localhost:8080"
    echo "  Mailpit: http://localhost:8025"
    echo "  Portainer: http://localhost:9000"
    echo ""
}

# 构建镜像
build_images() {
    log_info "构建 Docker 镜像..."
    cd "$SCRIPT_DIR"
    docker compose build --no-cache
    log_success "镜像构建完成！"
}

# 清理资源
clean_resources() {
    log_warning "清理 Docker 资源..."
    read -p "确定要清理未使用的镜像、容器和网络吗？(y/N): " confirm
    if [ "$confirm" = "y" ] || [ "$confirm" = "Y" ]; then
        cd "$SCRIPT_DIR"
        docker compose down -v
        docker system prune -f
        log_success "清理完成！"
    else
        log_info "取消清理"
    fi
}

# 进入容器 shell
enter_shell() {
    log_info "进入 App 容器..."
    cd "$SCRIPT_DIR"
    docker compose exec app bash
}

# 运行 Artisan 命令
run_artisan() {
    shift
    cd "$SCRIPT_DIR"
    docker compose exec app php artisan "$@"
}

# 运行 Composer 命令
run_composer() {
    shift
    cd "$SCRIPT_DIR"
    docker compose run --rm composer "$@"
}

# 主逻辑
case "${1:-help}" in
    start)
        start_services
        ;;
    stop)
        stop_services
        ;;
    restart)
        restart_services
        ;;
    logs)
        show_logs
        ;;
    status)
        show_status
        ;;
    build)
        build_images
        ;;
    clean)
        clean_resources
        ;;
    shell)
        enter_shell
        ;;
    artisan)
        run_artisan "$@"
        ;;
    composer)
        run_composer "$@"
        ;;
    help|*)
        show_help
        ;;
esac
