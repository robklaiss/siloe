# Siloe Deployment Guide

This guide provides comprehensive instructions for deploying the Siloe application to production servers.

## Prerequisites

- SSH access to the production server
- Git installed on your local machine
- Basic knowledge of shell commands

## Setup SSH Key Authentication (One-time setup)

For passwordless deployment, set up SSH key authentication:

```bash
# Run the SSH key setup script
bash deploy/scripts/manual_ssh_setup.sh
```

This script will:
1. Create an SSH key if it doesn't exist
2. Copy the public key to the server
3. Configure your SSH client
4. Test the connection
5. Update deployment scripts to use the key

## Deployment Configuration

All deployment settings are centralized in `deploy/config.sh`:

```bash
# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SSH_KEY_FILE="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY_FILE -o IdentitiesOnly=yes"

# Remote paths
REMOTE_BASE_DIR="/home1/siloecom/siloe"
REMOTE_PUBLIC_DIR="$REMOTE_BASE_DIR/public"
REMOTE_BACKUP_DIR="$REMOTE_BASE_DIR/backups"

# Rsync options
RSYNC_OPTS="-avz --delete --exclude-from=deploy/rsync_exclude.txt"

# Backup settings
BACKUP_COUNT=5  # Number of backups to keep

# Scripts to run after deployment
MIGRATION_SCRIPT="database/migrations/run_latest_migration.php"
PERMISSIONS_SCRIPT="deploy/scripts/set_permissions.sh"
```

Modify this file to match your server configuration.

## Deployment Methods

### 1. Unified Deployment Script (Recommended)

The unified deployment script provides a complete deployment workflow with error handling, backup/rollback, and verification:

```bash
# Standard deployment
bash deploy/deploy.sh

# Deployment with options
bash deploy/deploy.sh --dry-run        # Test without making changes
bash deploy/deploy.sh --verbose        # Show detailed output
bash deploy/deploy.sh --skip-backup    # Skip backup creation
bash deploy/deploy.sh --skip-migrations # Skip database migrations
bash deploy/deploy.sh --rollback       # Rollback to previous version
```

### 2. Legacy Deployment Scripts

For specific deployment tasks, you can use the legacy scripts:

```bash
# Deploy files only using rsync
bash deploy/scripts/deploy_rsync.sh

# Deploy with SCP (alternative method)
bash deploy/scripts/deploy_with_scp.sh

# Full application deployment (files + migrations + permissions)
bash deploy/scripts/deploy_full_application.sh
```

## Troubleshooting

### SSH Connection Issues

If you encounter SSH connection issues:

```bash
# Verify SSH connection
ssh -i ~/.ssh/siloe_ed25519 -o IdentitiesOnly=yes siloecom@192.185.143.154 "echo 'Connection test'"

# Regenerate SSH key setup
bash deploy/scripts/manual_ssh_setup.sh
```

### Database Issues

If you encounter database issues:

```bash
# Run the database fix script
bash deploy/scripts/fix_database.sh
```

### Server Structure Verification

To verify the server structure:

```bash
# Check server structure
bash deploy/scripts/check_server_structure.sh
```

## Complete System Restoration

If you need to completely restore the system:

```bash
# Run the complete fix script
bash deploy/scripts/complete_fix.sh
```

This script will:
1. Create necessary directories
2. Initialize the database
3. Create migration files
4. Set proper permissions
5. Verify the system is working

## Deployment Verification

After deployment, verify the system is working:

1. Visit http://siloecom.com to check the main application
2. Visit http://siloecom.com/system_test.php to run a system test
3. Check the logs for any errors

## Security Notes

- Always use SSH key authentication instead of passwords
- Keep your SSH private key secure
- Regularly rotate SSH keys for enhanced security
- Ensure proper file permissions on the server
- Back up the database regularly

## Advanced Configuration

### Customizing Rsync Exclusions

Edit `deploy/rsync_exclude.txt` to customize which files are excluded from deployment:

```
.git/
.gitignore
.DS_Store
node_modules/
vendor/
*.log
```

### Customizing Backup Settings

Modify the `BACKUP_COUNT` variable in `deploy/config.sh` to change the number of backups kept on the server.

## Continuous Integration

For automated deployments, you can integrate the deployment scripts with CI/CD systems:

```bash
# Example CI/CD command
SSH_OPTS="-i $SSH_KEY_FILE -o IdentitiesOnly=yes" bash deploy/deploy.sh --skip-backup
```

## Rollback Procedure

To rollback to a previous version:

```bash
# List available backups
ssh -i ~/.ssh/siloe_ed25519 -o IdentitiesOnly=yes siloecom@192.185.143.154 "ls -la ~/siloe/backups"

# Rollback to the most recent backup
bash deploy/deploy.sh --rollback

# Rollback to a specific backup
BACKUP_FILE="siloe_backup_20250825_204307.tar.gz" bash deploy/scripts/rollback.sh
```

## Conclusion

This deployment guide covers all aspects of deploying the Siloe application to production. By following these instructions, you can ensure a smooth and reliable deployment process with proper error handling, backup/rollback capabilities, and security measures.
