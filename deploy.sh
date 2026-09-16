#!/bin/bash

# Nexsus Link Tracker - Docker Deployment Script
# Usage: ./deploy.sh [build|start|stop|restart|logs|status]

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Functions
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Check if Docker is installed
check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
}

# Check if .env exists
check_env() {
    if [ ! -f .env ]; then
        print_warning ".env file not found. Creating from .env.example..."
        if [ -f .env.example ]; then
            cp .env.example .env
            print_warning "Please edit .env file with your configuration."
        else
            print_error ".env.example not found. Please create .env file."
            exit 1
        fi
    fi
}

# Generate APP_KEY if not set
generate_key() {
    if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
        print_warning "APP_KEY not set. Generating..."
        KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));" 2>/dev/null || echo "base64:$(openssl rand -base64 32)")
        sed -i "s/APP_KEY=/APP_KEY=$KEY/" .env 2>/dev/null || \
        echo "APP_KEY=$KEY" >> .env
        print_status "APP_KEY generated."
    fi
}

# Build and start containers
build() {
    check_docker
    check_env
    generate_key
    
    print_status "Building Docker images..."
    docker-compose build --no-cache
    
    print_status "Starting containers..."
    docker-compose up -d
    
    # Wait for database
    print_status "Waiting for database..."
    sleep 5
    
    # Run migrations
    print_status "Running migrations..."
    docker-compose exec app php artisan migrate --force
    
    # Seed database
    print_status "Seeding database..."
    docker-compose exec app php artisan db:seed --force 2>/dev/null || true
    
    # Cache config
    print_status "Caching configuration..."
    docker-compose exec app php artisan config:cache
    docker-compose exec app php artisan route:cache
    docker-compose exec app php artisan view:cache
    
    # Set permissions
    print_status "Setting permissions..."
    docker-compose exec app chmod -R 777 storage bootstrap/cache
    
    print_status "Deployment complete!"
    echo ""
    echo "Application is running at: http://localhost:${APP_PORT:-8080}"
    echo "Admin panel: http://localhost:${APP_PORT:-8080}/admin"
    echo "Login: admin / 12345678"
    echo ""
    echo "Useful commands:"
    echo "  docker-compose logs -f        # View logs"
    echo "  docker-compose exec app bash  # Enter container"
    echo "  docker-compose down           # Stop containers"
}

# Start containers
start() {
    check_docker
    print_status "Starting containers..."
    docker-compose up -d
    print_status "Containers started."
}

# Stop containers
stop() {
    check_docker
    print_status "Stopping containers..."
    docker-compose down
    print_status "Containers stopped."
}

# Restart containers
restart() {
    check_docker
    print_status "Restarting containers..."
    docker-compose restart
    print_status "Containers restarted."
}

# View logs
logs() {
    check_docker
    docker-compose logs -f
}

# Show status
status() {
    check_docker
    docker-compose ps
}

# Main
case "${1:-}" in
    build)
        build
        ;;
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    logs)
        logs
        ;;
    status)
        status
        ;;
    *)
        echo "Usage: $0 {build|start|stop|restart|logs|status}"
        echo ""
        echo "Commands:"
        echo "  build   - Build and start all containers"
        echo "  start   - Start existing containers"
        echo "  stop    - Stop all containers"
        echo "  restart - Restart all containers"
        echo "  logs    - View container logs"
        echo "  status  - Show container status"
        exit 1
        ;;
esac
