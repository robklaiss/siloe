#!/bin/bash
# Unified deployment script for Siloe
# This script handles the entire deployment process with error handling and backup/rollback functionality

set -euo pipefail

# Load configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${SCRIPT_DIR}/config.sh"

if [[ ! -f "$CONFIG_FILE" ]]; then
    echo "Error: Configuration file not found at $CONFIG_FILE"
    exit 1
fi

source "$CONFIG_FILE"

# Colors for output
GREEN="\033[0;32m"
YELLOW="\033[0;33m"
RED="\033[0;31m"
BLUE="\033[0;34m"
NC="\033[0m" # No Color

# Global variables
ROLLBACK_NEEDED=false
DEPLOYMENT_START_TIME=$(date +%s)
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
DRY_RUN=false
VERBOSE=false

# Function to display help
show_help() {
    echo "Siloe Deployment Script"
    echo "Usage: $0 [options]"
    echo ""
    echo "Options:"
    echo "  -h, --help              Show this help message"
    echo "  -d, --dry-run           Perform a dry run without making changes"
    echo "  -v, --verbose           Enable verbose output"
    echo "  -s, --skip-backup       Skip backup creation"
    echo "  -m, --skip-migrations   Skip running migrations"
    echo "  -p, --skip-permissions  Skip setting permissions"
    echo "  -r, --rollback [backup] Rollback to specified backup or latest if not specified"
    echo ""
}

# Function for logging
log() {
    local level=$1
    local message=$2
    local color=$NC
    local timestamp=$(date +"%Y-%m-%d %H:%M:%S")
    
    case $level in
        "INFO") color=$BLUE ;;
        "SUCCESS") color=$GREEN ;;
        "WARNING") color=$YELLOW ;;
        "ERROR") color=$RED ;;
    esac
    
    echo -e "${color}[$timestamp] [$level] $message${NC}"
    echo "[$timestamp] [$level] $message" >> "$DEPLOYMENT_LOG_FILE"
}

# Function to handle errors
handle_error() {
    local exit_code=$?
    local line_number=$1
    
    log "ERROR" "Error occurred at line $line_number with exit code $exit_code"
    
    if [[ "$ROLLBACK_NEEDED" == true ]]; then
        log "WARNING" "Deployment failed. Initiating rollback..."
        rollback
    fi
    
    log "ERROR" "Deployment failed. Check the log file at $DEPLOYMENT_LOG_FILE for details."
    exit $exit_code
}

# Set up error handling
trap 'handle_error $LINENO' ERR

# Function to check SSH connection
check_ssh_connection() {
    log "INFO" "Checking SSH connection to $REMOTE..."
    
    if ! ssh -o BatchMode=yes -o ConnectTimeout=5 $SSH_OPTS "$REMOTE" "echo 'SSH connection successful'" &>/dev/null; then
        log "ERROR" "Cannot connect to server via SSH. Please check your SSH configuration."
        log "INFO" "Make sure you've run the setup_ssh_key_improved.sh script to configure SSH key authentication."
        exit 1
    fi
    
    log "SUCCESS" "SSH connection successful"
}

# Function to check remote paths
check_remote_paths() {
    log "INFO" "Checking remote paths..."
    
    # Check if the base directory exists, create if not
    if ! ssh $SSH_OPTS "$REMOTE" "test -d '$REMOTE_BASE_PATH'" &>/dev/null; then
        log "WARNING" "Remote base path $REMOTE_BASE_PATH does not exist. Creating..."
        ssh $SSH_OPTS "$REMOTE" "mkdir -p '$REMOTE_BASE_PATH'"
    fi
    
    # Check if the public directory exists, create if not
    if ! ssh $SSH_OPTS "$REMOTE" "test -d '$REMOTE_PUBLIC_PATH'" &>/dev/null; then
        log "WARNING" "Remote public path $REMOTE_PUBLIC_PATH does not exist. Creating..."
        ssh $SSH_OPTS "$REMOTE" "mkdir -p '$REMOTE_PUBLIC_PATH'"
    fi
    
    # Create backup directory if it doesn't exist
    if ! ssh $SSH_OPTS "$REMOTE" "test -d '$REMOTE_BACKUP_PATH'" &>/dev/null; then
        log "INFO" "Creating backup directory at $REMOTE_BACKUP_PATH..."
        ssh $SSH_OPTS "$REMOTE" "mkdir -p '$REMOTE_BACKUP_PATH'"
    fi
    
    log "SUCCESS" "Remote paths verified"
}

# Function to create backup
create_backup() {
    if [[ "$BACKUP_ENABLED" != true ]]; then
        log "INFO" "Backup creation skipped as per configuration"
        return 0
    fi
    
    log "INFO" "Creating backup of remote files..."
    
    # Create backup on the remote server
    ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_BASE_PATH' && \
        tar -czf '$REMOTE_BACKUP_PATH/$BACKUP_NAME.tar.gz' \
        --exclude='backups' --exclude='storage/logs/*' --exclude='*.log' ."
    
    if [[ $? -ne 0 ]]; then
        log "ERROR" "Failed to create backup"
        return 1
    fi
    
    log "SUCCESS" "Backup created at $REMOTE_BACKUP_PATH/$BACKUP_NAME.tar.gz"
    
    # Clean up old backups
    log "INFO" "Cleaning up old backups, keeping last $BACKUP_KEEP_COUNT..."
    ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_BACKUP_PATH' && \
        ls -t *.tar.gz | tail -n +$((BACKUP_KEEP_COUNT+1)) | xargs rm -f"
    
    # Set the flag to indicate that rollback is possible
    ROLLBACK_NEEDED=true
    
    return 0
}

# Function to deploy files
deploy_files() {
    log "INFO" "Deploying files to server..."
    
    local rsync_cmd="rsync $RSYNC_OPTS"
    
    if [[ "$DRY_RUN" == true ]]; then
        rsync_cmd="$rsync_cmd --dry-run"
    fi
    
    if [[ "$VERBOSE" == true ]]; then
        rsync_cmd="$rsync_cmd -v"
    else
        rsync_cmd="$rsync_cmd -q"
    fi
    
    # Execute rsync command
    $rsync_cmd -e "ssh $SSH_OPTS" "${RSYNC_EXCLUDES[@]}" "$ROOT_DIR/" "$REMOTE:$REMOTE_BASE_PATH/"
    
    if [[ $? -ne 0 ]]; then
        log "ERROR" "Failed to deploy files"
        return 1
    fi
    
    log "SUCCESS" "Files deployed successfully"
    return 0
}

# Function to run migrations
run_migrations() {
    if [[ "$RUN_MIGRATIONS" != true ]]; then
        log "INFO" "Migrations skipped as per configuration"
        return 0
    fi
    
    log "INFO" "Running database migrations..."
    
    # Run the migration script, fall back to init_db.php if it fails
    ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_BASE_PATH' && \
        $PHP_BIN $MIGRATION_SCRIPT || $PHP_BIN $FALLBACK_MIGRATION_SCRIPT"
    
    if [[ $? -ne 0 ]]; then
        log "ERROR" "Failed to run migrations"
        return 1
    fi
    
    log "SUCCESS" "Migrations completed successfully"
    return 0
}

# Function to set permissions
set_permissions() {
    if [[ "$SET_PERMISSIONS" != true ]]; then
        log "INFO" "Permission setting skipped as per configuration"
        return 0
    fi
    
    log "INFO" "Setting file permissions..."
    
    # Run the permission script on the remote server
    ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_BASE_PATH' && bash $PERMISSION_SCRIPT"
    
    if [[ $? -ne 0 ]]; then
        log "ERROR" "Failed to set permissions"
        return 1
    fi
    
    log "SUCCESS" "Permissions set successfully"
    return 0
}

# Function to rollback to a previous backup
rollback() {
    local backup_file=""
    
    if [[ -n "${1:-}" ]]; then
        backup_file="$1"
    else
        # Get the latest backup
        backup_file=$(ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_BACKUP_PATH' && ls -t *.tar.gz | head -n 1")
    fi
    
    if [[ -z "$backup_file" ]]; then
        log "ERROR" "No backup found for rollback"
        return 1
    fi
    
    log "WARNING" "Rolling back to backup: $backup_file"
    
    # Extract the backup
    ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_BASE_PATH' && \
        tar -xzf '$REMOTE_BACKUP_PATH/$backup_file' --overwrite"
    
    if [[ $? -ne 0 ]]; then
        log "ERROR" "Failed to rollback to backup $backup_file"
        return 1
    fi
    
    # Set permissions after rollback
    set_permissions
    
    log "SUCCESS" "Rollback to $backup_file completed successfully"
    return 0
}

# Function to verify deployment
verify_deployment() {
    log "INFO" "Verifying deployment..."
    
    # Check if the public directory is accessible
    ssh $SSH_OPTS "$REMOTE" "test -d '$REMOTE_PUBLIC_PATH'" || {
        log "ERROR" "Public directory not found after deployment"
        return 1
    }
    
    # Check if index.php exists in the public directory
    ssh $SSH_OPTS "$REMOTE" "test -f '$REMOTE_PUBLIC_PATH/index.php'" || {
        log "ERROR" "index.php not found in public directory"
        return 1
    }
    
    log "SUCCESS" "Deployment verification passed"
    return 0
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -d|--dry-run)
            DRY_RUN=true
            log "INFO" "Dry run mode enabled"
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            log "INFO" "Verbose mode enabled"
            shift
            ;;
        -s|--skip-backup)
            BACKUP_ENABLED=false
            log "INFO" "Backup creation will be skipped"
            shift
            ;;
        -m|--skip-migrations)
            RUN_MIGRATIONS=false
            log "INFO" "Migrations will be skipped"
            shift
            ;;
        -p|--skip-permissions)
            SET_PERMISSIONS=false
            log "INFO" "Permission setting will be skipped"
            shift
            ;;
        -r|--rollback)
            if [[ -n "${2:-}" && "${2:0:1}" != "-" ]]; then
                rollback "$2"
                exit $?
                shift 2
            else
                rollback
                exit $?
                shift
            fi
            ;;
        *)
            log "ERROR" "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Initialize log file
mkdir -p "$(dirname "$DEPLOYMENT_LOG_FILE")"
echo "Siloe deployment log - $(date)" > "$DEPLOYMENT_LOG_FILE"

# Main deployment process
log "INFO" "Starting deployment process"

# Pre-deployment checks
check_ssh_connection
check_remote_paths

# Create backup before deployment
create_backup

# Deploy files
deploy_files

# Run migrations
run_migrations

# Set permissions
set_permissions

# Verify deployment
verify_deployment

# Calculate deployment time
DEPLOYMENT_END_TIME=$(date +%s)
DEPLOYMENT_DURATION=$((DEPLOYMENT_END_TIME - DEPLOYMENT_START_TIME))

log "SUCCESS" "Deployment completed successfully in $DEPLOYMENT_DURATION seconds"
log "INFO" "Log file: $DEPLOYMENT_LOG_FILE"

if [[ "$DRY_RUN" == true ]]; then
    log "WARNING" "This was a dry run. No actual changes were made."
fi

exit 0
