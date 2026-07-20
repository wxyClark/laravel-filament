#!/bin/bash
# ============================================
# Docker Environment Management Script
# Usage: ./docker-env.sh [command] [environment]
# ============================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"

# Functions
show_help() {
    echo -e "${BLUE}Docker Environment Management${NC}"
    echo ""
    echo "Usage: $0 [command] [environment]"
    echo ""
    echo "Commands:"
    echo "  up        Start services"
    echo "  down      Stop services"
    echo "  restart   Restart services"
    echo "  logs      View logs"
    echo "  ps        Show running containers"
    echo "  build     Build/rebuild images"
    echo "  shell     Access app container shell"
    echo "  artisan   Run artisan command"
    echo "  composer  Run composer command"
    echo "  test      Run tests"
    echo "  optimize  Optimize Laravel"
    echo "  backup    Backup database"
    echo "  restore   Restore database"
    echo ""
    echo "Environments:"
    echo "  dev       Development (default)"
    echo "  test      Testing"
    echo "  staging   Staging/Pre-production"
    echo "  prod      Production"
    echo ""
    echo "Examples:"
    echo "  $0 up dev          # Start development environment"
    echo "  $0 up prod         # Start production environment"
    echo "  $0 logs dev        # View development logs"
    echo "  $0 artisan dev migrate  # Run migrations in dev"
    echo "  $0 shell prod      # Access production shell"
}

# Get docker-compose files for environment
get_compose_files() {
    local env=$1
    case $env in
        dev|development)
            echo "-f docker-compose.base.yml -f docker-compose.dev.yml"
            ;;
        test|testing)
            echo "-f docker-compose.base.yml -f docker-compose.test.yml"
            ;;
        staging)
            echo "-f docker-compose.base.yml -f docker-compose.staging.yml"
            ;;
        prod|production)
            echo "-f docker-compose.base.yml -f docker-compose.prod.yml"
            ;;
        *)
            echo "-f docker-compose.base.yml -f docker-compose.dev.yml"
            ;;
    esac
}

# Get environment file
get_env_file() {
    local env=$1
    case $env in
        dev|development)
            echo ".env.dev"
            ;;
        test|testing)
            echo ".env.test"
            ;;
        staging)
            echo ".env.staging"
            ;;
        prod|production)
            echo ".env.prod"
            ;;
        *)
            echo ".env.dev"
            ;;
    esac
}

# Main command handler
command=$1
environment=${2:-dev}

case $command in
    up)
        echo -e "${GREEN}Starting $environment environment...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        ENV_FILE=$(get_env_file $environment)
        
        if [ ! -f "$ENV_FILE" ]; then
            echo -e "${YELLOW}Warning: $ENV_FILE not found. Using .env${NC}"
            ENV_FILE=".env"
        fi
        
        docker compose $COMPOSE_FILES --env-file $ENV_FILE up -d
        echo -e "${GREEN}$environment environment started!${NC}"
        ;;
    
    down)
        echo -e "${YELLOW}Stopping $environment environment...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES down
        ;;
    
    restart)
        echo -e "${YELLOW}Restarting $environment environment...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES restart
        ;;
    
    logs)
        echo -e "${BLUE}Viewing $environment logs...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES logs -f
        ;;
    
    ps)
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES ps
        ;;
    
    build)
        echo -e "${GREEN}Building $environment images...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES build --no-cache
        ;;
    
    shell)
        echo -e "${GREEN}Accessing $environment app shell...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES exec app sh
        ;;
    
    artisan)
        shift 2  # Remove command and environment
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES exec app php artisan "$@"
        ;;
    
    composer)
        shift 2
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES exec app composer "$@"
        ;;
    
    test)
        echo -e "${GREEN}Running tests in $environment...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES exec app ./vendor/bin/pest --compact
        ;;
    
    optimize)
        echo -e "${GREEN}Optimizing Laravel for $environment...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        docker compose $COMPOSE_FILES exec app php artisan config:cache
        docker compose $COMPOSE_FILES exec app php artisan route:cache
        docker compose $COMPOSE_FILES exec app php artisan view:cache
        docker compose $COMPOSE_FILES exec app php artisan event:cache
        ;;
    
    backup)
        echo -e "${GREEN}Backing up $environment database...${NC}"
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        mkdir -p backups
        COMPOSE_FILES=$(get_compose_files $environment)
        
        # Get database credentials from env file
        ENV_FILE=$(get_env_file $environment)
        if [ -f "$ENV_FILE" ]; then
            source $ENV_FILE
            DB_NAME=${DB_DATABASE:-laravel}
            DB_USER=${DB_USERNAME:-laravel}
            DB_PASS=${DB_PASSWORD:-secret}
        fi
        
        docker compose $COMPOSE_FILES exec -T mysql mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > "backups/${environment}_backup_${TIMESTAMP}.sql"
        echo -e "${GREEN}Backup saved to backups/${environment}_backup_${TIMESTAMP}.sql${NC}"
        ;;
    
    restore)
        if [ -z "$3" ]; then
            echo -e "${RED}Please specify backup file: $0 restore [env] [backup_file]${NC}"
            exit 1
        fi
        echo -e "${YELLOW}Restoring $environment database from $3...${NC}"
        COMPOSE_FILES=$(get_compose_files $environment)
        ENV_FILE=$(get_env_file $environment)
        
        if [ -f "$ENV_FILE" ]; then
            source $ENV_FILE
            DB_NAME=${DB_DATABASE:-laravel}
            DB_USER=${DB_USERNAME:-laravel}
            DB_PASS=${DB_PASSWORD:-secret}
        fi
        
        docker compose $COMPOSE_FILES exec -T mysql mysql -u $DB_USER -p$DB_PASS $DB_NAME < "$3"
        echo -e "${GREEN}Database restored!${NC}"
        ;;
    
    *)
        show_help
        ;;
esac
