#!/bin/bash
# Manual SSH key setup script for Siloe deployment
# This script helps manually set up SSH key authentication when automatic methods fail

set -euo pipefail

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

log "INFO" "Siloe Manual SSH Setup Script"
echo "=================================="

# Step 1: Check for existing keys
if [[ -f "${SSH_KEY_FILE}" ]]; then
    log "INFO" "Using existing key at ${SSH_KEY_FILE}"
    
    # Display public key
    log "SUCCESS" "Your public SSH key:"
    echo "=================================="
    cat "${SSH_KEY_FILE}.pub"
    echo "=================================="
else
    log "INFO" "Creating a new SSH key..."
    ssh-keygen -t $SSH_KEY_TYPE -f "$SSH_KEY_FILE" -C "siloe_deploy_key"
    
    # Display public key
    log "SUCCESS" "Your public SSH key:"
    echo "=================================="
    cat "${SSH_KEY_FILE}.pub"
    echo "=================================="
fi

# Step 2: Create a server setup script
log "INFO" "Creating server setup script..."
SERVER_SCRIPT=$(mktemp)

cat > "$SERVER_SCRIPT" << 'EOF'
#!/bin/bash
# Server-side SSH key setup script

# Create .ssh directory if it doesn't exist
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Create or append to authorized_keys
touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Add the key to authorized_keys
echo "$1" >> ~/.ssh/authorized_keys

# Show the result
echo "Key added to authorized_keys:"
grep -F "$1" ~/.ssh/authorized_keys
EOF

log "SUCCESS" "Server script created"

# Step 3: Copy the script to the server
log "INFO" "Copying setup script to server..."
log "INFO" "You will be prompted for your password"

# Use scp with only password authentication
scp -o PreferredAuthentications=password "$SERVER_SCRIPT" "$SERVER:~/ssh_setup.sh"

if [[ $? -ne 0 ]]; then
    log "ERROR" "Failed to copy script to server"
    rm "$SERVER_SCRIPT"
    exit 1
fi

# Step 4: Make the script executable and run it
log "INFO" "Running setup script on server..."
log "INFO" "You will be prompted for your password again"

# Get the public key content
PUBLIC_KEY=$(cat "${SSH_KEY_FILE}.pub")

# Run the script on the server with the public key as argument
ssh -o PreferredAuthentications=password "$SERVER" "chmod +x ~/ssh_setup.sh && bash ~/ssh_setup.sh '$PUBLIC_KEY'"

if [[ $? -ne 0 ]]; then
    log "ERROR" "Failed to run setup script on server"
    rm "$SERVER_SCRIPT"
    exit 1
fi

# Step 5: Clean up
log "INFO" "Cleaning up..."
rm "$SERVER_SCRIPT"
ssh -o PreferredAuthentications=password "$SERVER" "rm ~/ssh_setup.sh"

# Step 6: Update SSH config
if [[ -f "$SSH_CONFIG_FILE" ]]; then
    if grep -q "Host $SERVER_ALIAS" "$SSH_CONFIG_FILE"; then
        log "INFO" "Updating existing SSH config entry..."
        # Create a temporary file for the new config
        TMP_CONFIG=$(mktemp)
        
        # Write the updated config to the temporary file
        awk -v host="$SERVER_ALIAS" -v file="$SSH_KEY_FILE" '
        {
            if ($1 == "Host" && $2 == host) {
                in_host_block = 1
                print $0
            } else if (in_host_block && $1 == "IdentityFile") {
                print "    IdentityFile " file
            } else if (in_host_block && $1 == "Host") {
                in_host_block = 0
                print $0
            } else {
                print $0
            }
        }' "$SSH_CONFIG_FILE" > "$TMP_CONFIG"
        
        # Replace the original config with the updated one
        mv "$TMP_CONFIG" "$SSH_CONFIG_FILE"
        chmod 600 "$SSH_CONFIG_FILE"
    else
        log "INFO" "Adding server to SSH config..."
        cat >> "$SSH_CONFIG_FILE" << EOF

# Siloe server
Host $SERVER_ALIAS
    HostName $SERVER_HOST
    User $SERVER_USER
    IdentityFile $SSH_KEY_FILE
    IdentitiesOnly yes
    ServerAliveInterval 60
    ServerAliveCountMax 30
EOF
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
    IdentitiesOnly yes
    ServerAliveInterval 60
    ServerAliveCountMax 30
EOF
    chmod 600 "$SSH_CONFIG_FILE"
fi

# Step 7: Test the connection
log "INFO" "Testing SSH connection..."
if ssh -o BatchMode=yes -o ConnectTimeout=5 -o IdentitiesOnly=yes -i "$SSH_KEY_FILE" "$SERVER" "echo 'SSH connection successful!'" 2>/dev/null; then
    log "SUCCESS" "SSH connection successful! Passwordless authentication is working."
else
    log "WARNING" "Could not connect using passwordless authentication."
    log "INFO" "Please try connecting manually with: ssh -i $SSH_KEY_FILE -o IdentitiesOnly=yes $SERVER"
    log "INFO" "You may need to enter your password one more time to set up the connection."
fi

# Step 8: Update deploy scripts
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
log "INFO" "Or using the alias: ssh $SERVER_ALIAS"
log "INFO" "Deployment scripts have been updated to use the SSH key."
echo
