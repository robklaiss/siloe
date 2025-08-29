#!/bin/bash
# Improved SSH key setup script for Siloe deployment
# This script creates an SSH key, copies it to the server, and configures SSH for passwordless authentication

set -euo pipefail

# Default options
NON_INTERACTIVE=false
USE_EXISTING=false
COPY_KEY=true

# Colors for output
GREEN="\033[0;32m"
YELLOW="\033[0;33m"
RED="\033[0;31m"
BLUE="\033[0;34m"
NC="\033[0m" # No Color

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SSH_KEY_TYPE="ed25519"
SSH_KEY_FILE="$HOME/.ssh/siloe_${SSH_KEY_TYPE}"
SSH_CONFIG_FILE="$HOME/.ssh/config"
SERVER_ALIAS="siloe"

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -y|--yes)
            NON_INTERACTIVE=true
            USE_EXISTING=true
            COPY_KEY=true
            shift
            ;;
        -h|--help)
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  -y, --yes       Non-interactive mode, automatically answer yes to all prompts"
            echo "  -h, --help      Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            echo "Use -h or --help for usage information"
            exit 1
            ;;
    esac
done

# Log function
log() {
    local level=$1
    local message=$2
    local color=$NC
    
    case $level in
        "INFO") color=$BLUE ;;
        "SUCCESS") color=$GREEN ;;
        "WARNING") color=$YELLOW ;;
        "ERROR") color=$RED ;;
    esac
    
    echo -e "${color}[$level] $message${NC}"
}

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check if ssh-keygen is available
if ! command_exists ssh-keygen; then
    log "ERROR" "ssh-keygen command not found. Please install OpenSSH client."
    exit 1
fi

log "INFO" "Siloe SSH Setup Script"
echo "=================================="

# Check for existing keys
if [[ -f "${SSH_KEY_FILE}" ]]; then
    log "WARNING" "Key already exists at ${SSH_KEY_FILE}"
    if [[ "$NON_INTERACTIVE" == false ]]; then
        read -p "Do you want to use this key? (y/n): " use_existing_input
        [[ "$use_existing_input" == "y" ]] && USE_EXISTING=true
    else
        log "INFO" "Non-interactive mode: Using existing key"
    fi
    
    if [[ "$USE_EXISTING" != true ]]; then
        log "INFO" "Creating a new SSH key..."
        if [[ "$NON_INTERACTIVE" == true ]]; then
            ssh-keygen -t $SSH_KEY_TYPE -f "$SSH_KEY_FILE" -C "siloe_deploy_key" -N "" -q
        else
            ssh-keygen -t $SSH_KEY_TYPE -f "$SSH_KEY_FILE" -C "siloe_deploy_key"
        fi
    fi
else
    log "INFO" "Creating a new SSH key..."
    if [[ "$NON_INTERACTIVE" == true ]]; then
        ssh-keygen -t $SSH_KEY_TYPE -f "$SSH_KEY_FILE" -C "siloe_deploy_key" -N "" -q
    else
        ssh-keygen -t $SSH_KEY_TYPE -f "$SSH_KEY_FILE" -C "siloe_deploy_key"
    fi
fi

# Display public key
log "SUCCESS" "Your public SSH key:"
echo "=================================="
cat "${SSH_KEY_FILE}.pub"
echo "=================================="

# Ask if user wants to copy the key to the server
if [[ "$NON_INTERACTIVE" == false ]]; then
    read -p "Do you want to copy the key to the server? (y/n): " copy_key_input
    [[ "$copy_key_input" == "y" ]] && COPY_KEY=true || COPY_KEY=false
else
    log "INFO" "Non-interactive mode: Copying key to server"
fi

if [[ "$COPY_KEY" == true ]]; then
    # Check if ssh-copy-id is available
    if command_exists ssh-copy-id; then
        log "INFO" "Copying SSH key to server..."
        ssh-copy-id -i "${SSH_KEY_FILE}.pub" -o IdentitiesOnly=yes "$SERVER"
    else
        log "WARNING" "ssh-copy-id not found. Using manual method..."
        
        # Create a temporary script to run on the server
        TMP_SCRIPT=$(mktemp)
        cat > "$TMP_SCRIPT" << 'EOF'
#!/bin/bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
cat >> ~/.ssh/authorized_keys
EOF
        
        log "INFO" "Please enter your server password when prompted"
        cat "${SSH_KEY_FILE}.pub" | ssh "$SERVER" "bash -s" < "$TMP_SCRIPT"
        rm "$TMP_SCRIPT"
    fi
    
    log "SUCCESS" "SSH key copied to server"
else
    log "INFO" "Manual instructions to add your key to the server:"
    echo "1. Log in to your server via SSH: ssh ${SERVER_USER}@${SERVER_HOST}"
    echo "2. Create the ~/.ssh directory if it doesn't exist: mkdir -p ~/.ssh"
    echo "3. Set proper permissions: chmod 700 ~/.ssh"
    echo "4. Add your key to authorized_keys: nano ~/.ssh/authorized_keys"
    echo "5. Paste the key shown above into the file"
    echo "6. Set correct permissions: chmod 600 ~/.ssh/authorized_keys"
fi

# Add to SSH config
if [[ -f "$SSH_CONFIG_FILE" ]]; then
    if grep -q "Host $SERVER_ALIAS" "$SSH_CONFIG_FILE"; then
        log "WARNING" "SSH config entry for $SERVER_ALIAS already exists"
    else
        log "INFO" "Adding server to SSH config..."
        cat >> "$SSH_CONFIG_FILE" << EOF

# Siloe server
Host $SERVER_ALIAS
    HostName $SERVER_HOST
    User $SERVER_USER
    IdentityFile $SSH_KEY_FILE
    ServerAliveInterval 60
    ServerAliveCountMax 30
EOF
        log "SUCCESS" "Added server to SSH config successfully"
    fi
else
    log "INFO" "Creating SSH config..."
    mkdir -p "$HOME/.ssh"
    chmod 700 "$HOME/.ssh"
    cat > "$SSH_CONFIG_FILE" << EOF
# Siloe server
Host $SERVER_ALIAS
    HostName $SERVER_HOST
    User $SERVER_USER
    IdentityFile $SSH_KEY_FILE
    ServerAliveInterval 60
    ServerAliveCountMax 30
EOF
    chmod 600 "$SSH_CONFIG_FILE"
    log "SUCCESS" "Created SSH config successfully"
fi

# Test the connection
log "INFO" "Testing SSH connection..."
if ssh -o BatchMode=yes -o ConnectTimeout=5 -o IdentitiesOnly=yes -i "$SSH_KEY_FILE" "$SERVER" "echo 'SSH connection successful!'" 2>/dev/null; then
    log "SUCCESS" "SSH connection successful! Passwordless authentication is working."
else
    log "WARNING" "Could not connect using passwordless authentication."
    log "INFO" "Please try connecting manually with: ssh -i $SSH_KEY_FILE -o IdentitiesOnly=yes $SERVER"
    log "INFO" "You may need to enter your password one more time to set up the connection."
fi

# Update deploy scripts
log "INFO" "Updating deployment scripts to use the SSH key..."

# Find all deployment scripts that might need updating
DEPLOY_SCRIPTS=(
    "deploy_rsync.sh"
    "deploy_with_scp.sh"
    "deploy_full_application.sh"
)

for script in "${DEPLOY_SCRIPTS[@]}"; do
    SCRIPT_PATH="$(dirname "$0")/$script"
    if [[ -f "$SCRIPT_PATH" ]]; then
        log "INFO" "Checking $script..."
        
        # Check if the script contains SSH_OPTS
        if grep -q "SSH_OPTS" "$SCRIPT_PATH"; then
            # Backup the original script
            cp "$SCRIPT_PATH" "${SCRIPT_PATH}.bak"
            
            # Update the SSH_OPTS line
            sed -i.tmp "s|: \"\${SSH_OPTS:=.*}\"$|: \"\${SSH_OPTS:=-i $SSH_KEY_FILE -o IdentitiesOnly=yes}\"|g" "$SCRIPT_PATH"
            rm -f "${SCRIPT_PATH}.tmp"
            
            log "SUCCESS" "Updated $script to use the SSH key"
        else
            log "WARNING" "$script does not contain SSH_OPTS configuration"
        fi
    fi
done

log "SUCCESS" "Setup complete!"
log "INFO" "You can now connect to the server using: ssh -i $SSH_KEY_FILE -o IdentitiesOnly=yes $SERVER"
log "INFO" "Deployment scripts have been updated to use the SSH key."
echo
