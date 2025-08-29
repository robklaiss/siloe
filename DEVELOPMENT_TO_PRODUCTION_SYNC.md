# Siloe Development to Production Sync Workflow

## Overview
This document outlines the workflow for developing features locally and syncing them to production when ready.

## Workflow Steps

### 1. Development Phase
- Work on features/fixes in local development environment
- Test thoroughly on `http://localhost:8000`
- When a feature/fix is complete and tested, mark it for deployment

### 2. Marking Files for Deployment
Create/update `PENDING_DEPLOYMENT.md` with files ready for production:

```markdown
# Pending Deployment

## Ready for Production
- [ ] app/Controllers/MenuController.php - Fixed create method HTTP 500 error
- [ ] app/views/menus/create.php - Updated form validation
- [ ] database/migrations/xxx.php - New menu fields migration

## Deployed to Production
- [x] app/Core/Router.php - Fixed routing system
- [x] app/Controllers/Admin/DashboardController.php - Dashboard improvements
```

### 3. Deployment Commands

#### Deploy Specific Files
```bash
# Deploy single file
scp -i ~/.ssh/siloe_ed25519 -o IdentitiesOnly=yes /Users/robinklaiss/Dev/siloe/app/Controllers/MenuController.php siloecom@192.185.143.154:/home1/siloecom/siloe/app/Controllers/

# Deploy multiple files
scp -i ~/.ssh/siloe_ed25519 -o IdentitiesOnly=yes /Users/robinklaiss/Dev/siloe/app/Controllers/MenuController.php /Users/robinklaiss/Dev/siloe/app/views/menus/create.php siloecom@192.185.143.154:/home1/siloecom/siloe/app/Controllers/
```

#### Deploy Complete System (if major changes)
```bash
./deploy/scripts/deploy_complete_dev_system.sh
```

#### Deploy Specific Components
```bash
# Controllers only
scp -r -i ~/.ssh/siloe_ed25519 -o IdentitiesOnly=yes /Users/robinklaiss/Dev/siloe/app/Controllers/ siloecom@192.185.143.154:/home1/siloecom/siloe/app/

# Views only
scp -r -i ~/.ssh/siloe_ed25519 -o IdentitiesOnly=yes /Users/robinklaiss/Dev/siloe/app/views/ siloecom@192.185.143.154:/home1/siloecom/siloe/app/

# Database migrations
scp -r -i ~/.ssh/siloe_ed25519 -o IdentitiesOnly=yes /Users/robinklaiss/Dev/siloe/database/ siloecom@192.185.143.154:/home1/siloecom/siloe/
```

### 4. Testing in Production
After deployment:
1. Test the specific functionality on https://www.siloe.com.py
2. Check error logs: `ssh -i ~/.ssh/siloe_ed25519 siloecom@192.185.143.154 "tail -n 20 /home1/siloecom/public_html/error_log"`
3. If successful, move items from "Ready for Production" to "Deployed to Production" in `PENDING_DEPLOYMENT.md`

### 5. Rollback (if needed)
```bash
# Restore from backup
ssh -i ~/.ssh/siloe_ed25519 siloecom@192.185.143.154 "cp /home1/siloecom/siloe/backup_YYYYMMDD_HHMMSS/path/to/file /home1/siloecom/siloe/path/to/file"
```

## Server Details
- **SSH User**: siloecom
- **Server IP**: 192.185.143.154
- **SSH Key**: ~/.ssh/siloe_ed25519
- **Web Root**: /home1/siloecom/public_html
- **App Root**: /home1/siloecom/siloe
- **Admin URL**: https://www.siloe.com.py/admin/dashboard
- **Login URL**: https://www.siloe.com.py/emergency_login.php

## Quick Commands Reference
```bash
# Check server status
ssh -i ~/.ssh/siloe_ed25519 siloecom@192.185.143.154 "ls -la /home1/siloecom/siloe"

# View error logs
ssh -i ~/.ssh/siloe_ed25519 siloecom@192.185.143.154 "tail -n 50 /home1/siloecom/public_html/error_log"

# Set permissions after deployment
ssh -i ~/.ssh/siloe_ed25519 siloecom@192.185.143.154 "find /home1/siloecom/siloe -type f -exec chmod 644 {} \; && find /home1/siloecom/siloe -type d -exec chmod 755 {} \;"

# Create backup before major changes
ssh -i ~/.ssh/siloe_ed25519 siloecom@192.185.143.154 "cp -r /home1/siloecom/siloe /home1/siloecom/backup_$(date +%Y%m%d_%H%M%S)"
```

## Best Practices
1. Always test locally first
2. Deploy during low-traffic periods
3. Create backups before major deployments
4. Test one feature at a time in production
5. Keep `PENDING_DEPLOYMENT.md` updated
6. Monitor error logs after deployment
7. Have a rollback plan ready
