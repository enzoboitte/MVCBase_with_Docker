#!/bin/bash

# ==========================================
# Script de lancement - WebGestion Pro
# ==========================================

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction d'affichage
print_header() {
    echo ""
    echo -e "${BLUE}==========================================${NC}"
    echo -e "${BLUE}   WebGestion Pro - Docker Launcher${NC}"
    echo -e "${BLUE}==========================================${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Vérification de Docker
check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker n'est pas installé!"
        echo "Installez Docker: https://docs.docker.com/get-docker/"
        exit 1
    fi
    print_success "Docker est installé"

    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose n'est pas installé!"
        exit 1
    fi
    print_success "Docker Compose est installé"
}

# Détermine la commande docker compose
get_compose_cmd() {
    if docker compose version &> /dev/null 2>&1; then
        echo "docker compose"
    else
        echo "docker-compose"
    fi
}

COMPOSE_CMD=$(get_compose_cmd)

# Actions
start() {
    print_header
    check_docker
    
    print_info "Création des répertoires..."
    mkdir -p storage/logs storage/cache public/uploads
    chmod -R 775 storage public/uploads 2>/dev/null || true
    
    print_info "Démarrage des conteneurs..."
    $COMPOSE_CMD up -d --build
    
    echo ""
    print_success "WebGestion Pro est lancé!"
    echo ""
    echo -e "${GREEN}╔════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║  🌐 Application:  http://localhost:8080    ║${NC}"
    echo -e "${GREEN}║  🗄️  phpMyAdmin:   http://localhost:8081    ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════╝${NC}"
    echo ""
    print_info "Utilisez './start.sh logs' pour voir les logs"
    print_info "Utilisez './start.sh stop' pour arrêter"
}

stop() {
    print_info "Arrêt des conteneurs..."
    $COMPOSE_CMD down
    print_success "Conteneurs arrêtés"
}

restart() {
    print_info "Redémarrage des conteneurs..."
    $COMPOSE_CMD restart
    print_success "Conteneurs redémarrés"
}

logs() {
    $COMPOSE_CMD logs -f
}

status() {
    $COMPOSE_CMD ps
}

clean() {
    print_warning "Suppression des conteneurs et volumes..."
    $COMPOSE_CMD down -v --remove-orphans
    print_success "Nettoyage terminé"
}

rebuild() {
    print_info "Reconstruction des images..."
    $COMPOSE_CMD up -d --build --force-recreate
    print_success "Reconstruction terminée"
}

shell_php() {
    docker exec -it webgestion_php bash
}

shell_mysql() {
    docker exec -it webgestion_mysql mysql -uwebgestion -pwebgestion_secret webgestion
}

# Menu d'aide
show_help() {
    echo "Usage: ./start.sh [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  start     Démarre tous les conteneurs (par défaut)"
    echo "  stop      Arrête tous les conteneurs"
    echo "  restart   Redémarre tous les conteneurs"
    echo "  logs      Affiche les logs en temps réel"
    echo "  status    Affiche le statut des conteneurs"
    echo "  clean     Supprime les conteneurs et les volumes"
    echo "  rebuild   Reconstruit et relance les conteneurs"
    echo "  shell     Ouvre un shell dans le conteneur PHP"
    echo "  mysql     Ouvre un shell MySQL"
    echo "  help      Affiche cette aide"
}

# Point d'entrée principal
case "${1:-start}" in
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
    clean)
        clean
        ;;
    rebuild)
        rebuild
        ;;
    shell)
        shell_php
        ;;
    mysql)
        shell_mysql
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        print_error "Commande inconnue: $1"
        show_help
        exit 1
        ;;
esac
