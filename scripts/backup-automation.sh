#!/bin/bash

# Database Backup Automation Script
# Usage: ./backup-automation.sh [schedule-type]
# Schedule types: daily, weekly, monthly, custom

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
LOG_FILE="$PROJECT_DIR/storage/logs/backup-automation.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Error handling
handle_error() {
    log "ERROR: $1"
    echo -e "${RED}❌ Error: $1${NC}"
    exit 1
}

# Success logging
log_success() {
    log "SUCCESS: $1"
    echo -e "${GREEN}✅ $1${NC}"
}

# Info logging
log_info() {
    log "INFO: $1"
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Warning logging
log_warning() {
    log "WARNING: $1"
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Check if Laravel project exists
check_laravel_project() {
    if [ ! -f "$PROJECT_DIR/artisan" ]; then
        handle_error "Laravel project not found in $PROJECT_DIR"
    fi

    if [ ! -f "$PROJECT_DIR/composer.json" ]; then
        handle_error "composer.json not found - not a valid Laravel project"
    fi
}

# Check dependencies
check_dependencies() {
    log_info "Checking dependencies..."

    # Check if mysqldump is available
    if ! command -v mysqldump &> /dev/null; then
        handle_error "mysqldump not found. Please install MySQL client tools"
    fi

    # Check if gzip is available for compression
    if ! command -v gzip &> /dev/null; then
        log_warning "gzip not found. Backups will not be compressed"
    fi

    # Check PHP
    if ! command -v php &> /dev/null; then
        handle_error "PHP not found"
    fi

    log_success "All dependencies are available"
}

# Daily backup
daily_backup() {
    log_info "Starting daily backup..."

    cd "$PROJECT_DIR" || handle_error "Cannot change to project directory"

    # Full backup with compression and cleanup
    php artisan db:backup --type=full --compress --cleanup || handle_error "Daily backup failed"

    log_success "Daily backup completed"
}

# Weekly backup
weekly_backup() {
    log_info "Starting weekly backup..."

    cd "$PROJECT_DIR" || handle_error "Cannot change to project directory"

    # Full backup with compression, upload, and cleanup
    php artisan db:backup --type=full --compress --upload --cleanup --name="weekly_backup" || handle_error "Weekly backup failed"

    log_success "Weekly backup completed"
}

# Monthly backup
monthly_backup() {
    log_info "Starting monthly backup..."

    cd "$PROJECT_DIR" || handle_error "Cannot change to project directory"

    # Full backup with compression and upload (no cleanup for monthly)
    php artisan db:backup --type=full --compress --upload --name="monthly_backup" || handle_error "Monthly backup failed"

    log_success "Monthly backup completed"
}

# Custom backup
custom_backup() {
    log_info "Starting custom backup..."

    echo "Custom Backup Configuration"
    echo "==========================="

    # Backup type selection
    echo "Select backup type:"
    echo "1) Full (structure + data)"
    echo "2) Structure only"
    echo "3) Data only"
    read -p "Enter choice [1-3]: " backup_type_choice

    case $backup_type_choice in
        1) backup_type="full" ;;
        2) backup_type="structure" ;;
        3) backup_type="data" ;;
        *) backup_type="full" ;;
    esac

    # Compression option
    read -p "Compress backup? [y/N]: " compress_choice
    compress_option=""
    if [[ $compress_choice =~ ^[Yy]$ ]]; then
        compress_option="--compress"
    fi

    # Upload option
    read -p "Upload to cloud? [y/N]: " upload_choice
    upload_option=""
    if [[ $upload_choice =~ ^[Yy]$ ]]; then
        upload_option="--upload"
    fi

    # Cleanup option
    read -p "Cleanup old backups? [y/N]: " cleanup_choice
    cleanup_option=""
    if [[ $cleanup_choice =~ ^[Yy]$ ]]; then
        cleanup_option="--cleanup"
    fi

    # Custom name
    read -p "Custom backup name (optional): " custom_name
    name_option=""
    if [ -n "$custom_name" ]; then
        name_option="--name=$custom_name"
    fi

    cd "$PROJECT_DIR" || handle_error "Cannot change to project directory"

    # Execute custom backup
    php artisan db:backup --type=$backup_type $compress_option $upload_option $cleanup_option $name_option || handle_error "Custom backup failed"

    log_success "Custom backup completed"
}

# Setup cron jobs
setup_cron() {
    log_info "Setting up automated backup schedule..."

    # Check if crontab is available
    if ! command -v crontab &> /dev/null; then
        handle_error "crontab not found. Cannot setup automated scheduling"
    fi

    BACKUP_SCRIPT="$SCRIPT_DIR/backup-automation.sh"

    # Create temporary cron file
    TEMP_CRON=$(mktemp)

    # Get existing cron jobs
    crontab -l 2>/dev/null > "$TEMP_CRON" || true

    # Remove existing backup automation entries
    grep -v "backup-automation.sh" "$TEMP_CRON" > "${TEMP_CRON}.new" || true
    mv "${TEMP_CRON}.new" "$TEMP_CRON"

    # Add new cron jobs
    echo "# Database Backup Automation" >> "$TEMP_CRON"
    echo "# Daily backup at 2:00 AM" >> "$TEMP_CRON"
    echo "0 2 * * * $BACKUP_SCRIPT daily >> $LOG_FILE 2>&1" >> "$TEMP_CRON"
    echo "# Weekly backup on Sunday at 3:00 AM" >> "$TEMP_CRON"
    echo "0 3 * * 0 $BACKUP_SCRIPT weekly >> $LOG_FILE 2>&1" >> "$TEMP_CRON"
    echo "# Monthly backup on 1st at 4:00 AM" >> "$TEMP_CRON"
    echo "0 4 1 * * $BACKUP_SCRIPT monthly >> $LOG_FILE 2>&1" >> "$TEMP_CRON"
    echo "" >> "$TEMP_CRON"

    # Install new cron jobs
    crontab "$TEMP_CRON" || handle_error "Failed to install cron jobs"

    # Cleanup
    rm "$TEMP_CRON"

    log_success "Backup automation scheduled successfully"
    echo "Scheduled backups:"
    echo "  - Daily: Every day at 2:00 AM"
    echo "  - Weekly: Every Sunday at 3:00 AM"
    echo "  - Monthly: 1st of every month at 4:00 AM"
}

# Remove cron jobs
remove_cron() {
    log_info "Removing backup automation schedule..."

    if ! command -v crontab &> /dev/null; then
        log_warning "crontab not found"
        return
    fi

    # Create temporary cron file
    TEMP_CRON=$(mktemp)

    # Get existing cron jobs and remove backup automation entries
    crontab -l 2>/dev/null | grep -v "backup-automation.sh" > "$TEMP_CRON" || true

    # Install cleaned cron jobs
    crontab "$TEMP_CRON" || log_warning "Failed to update cron jobs"

    # Cleanup
    rm "$TEMP_CRON"

    log_success "Backup automation schedule removed"
}

# Show status
show_status() {
    echo "Database Backup Automation Status"
    echo "================================="
    echo ""

    # Check project status
    if [ -f "$PROJECT_DIR/artisan" ]; then
        echo "✅ Laravel project: Found"
    else
        echo "❌ Laravel project: Not found"
    fi

    # Check backup directory
    if [ -d "$PROJECT_DIR/storage/app/backups" ]; then
        backup_count=$(ls -1 "$PROJECT_DIR/storage/app/backups"/*.sql* 2>/dev/null | wc -l)
        echo "✅ Backup directory: Found ($backup_count backups)"
    else
        echo "❌ Backup directory: Not found"
    fi

    # Check dependencies
    command -v mysqldump &> /dev/null && echo "✅ mysqldump: Available" || echo "❌ mysqldump: Not found"
    command -v gzip &> /dev/null && echo "✅ gzip: Available" || echo "❌ gzip: Not found"
    command -v php &> /dev/null && echo "✅ PHP: Available" || echo "❌ PHP: Not found"

    # Check cron jobs
    if command -v crontab &> /dev/null; then
        if crontab -l 2>/dev/null | grep -q "backup-automation.sh"; then
            echo "✅ Automation: Scheduled"
        else
            echo "❌ Automation: Not scheduled"
        fi
    else
        echo "❌ crontab: Not available"
    fi

    echo ""
    echo "Recent backups:"
    if [ -d "$PROJECT_DIR/storage/app/backups" ]; then
        ls -la "$PROJECT_DIR/storage/app/backups"/*.sql* 2>/dev/null | tail -5 || echo "No backups found"
    fi
}

# Main function
main() {
    echo "🗄️  Database Backup Automation"
    echo "=============================="
    echo ""

    # Check Laravel project
    check_laravel_project

    # Check dependencies
    check_dependencies

    # Handle command line arguments
    case "${1:-interactive}" in
        "daily")
            daily_backup
            ;;
        "weekly")
            weekly_backup
            ;;
        "monthly")
            monthly_backup
            ;;
        "custom")
            custom_backup
            ;;
        "setup-cron")
            setup_cron
            ;;
        "remove-cron")
            remove_cron
            ;;
        "status")
            show_status
            ;;
        "interactive"|*)
            echo "Select an option:"
            echo "1) Daily backup"
            echo "2) Weekly backup"
            echo "3) Monthly backup"
            echo "4) Custom backup"
            echo "5) Setup automation (cron)"
            echo "6) Remove automation"
            echo "7) Show status"
            echo "8) Exit"

            read -p "Enter choice [1-8]: " choice

            case $choice in
                1) daily_backup ;;
                2) weekly_backup ;;
                3) monthly_backup ;;
                4) custom_backup ;;
                5) setup_cron ;;
                6) remove_cron ;;
                7) show_status ;;
                8) echo "Goodbye!" ;;
                *) echo "Invalid choice" ;;
            esac
            ;;
    esac
}

# Run main function
main "$@"
