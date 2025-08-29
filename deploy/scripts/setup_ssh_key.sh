#!/bin/bash
# Script to set up SSH key authentication for Siloe server

# Colors for output
GREEN="\033[0;32m"
YELLOW="\033[0;33m"
RED="\033[0;31m"
NC="\033[0m" # No Color

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY_TYPE="ed25519"
SSH_KEY_FILE="$HOME/.ssh/siloe_${SSH_KEY_TYPE}"
SSH_CONFIG_FILE="$HOME/.ssh/config"
SERVER_ALIAS="siloe"

echo -e "${GREEN}Siloe SSH Setup Script${NC}"
echo "=================================="
echo

# Check for existing keys
if [[ -f "${SSH_KEY_FILE}" ]]; then
    echo -e "${YELLOW}Key already exists at ${SSH_KEY_FILE}${NC}"
    read -p "Do you want to use this key? (y/n): " use_existing
    if [[ "$use_existing" != "y" ]]; then
        echo -e "${YELLOW}Creating a new SSH key...${NC}"
        ssh-keygen -t $SSH_KEY_TYPE -f "$SSH_KEY_FILE" -C "siloe_deploy_key"
    fi
else
    echo -e "${YELLOW}Creating a new SSH key...${NC}"
    ssh-keygen -t $SSH_KEY_TYPE -f "$SSH_KEY_FILE" -C "siloe_deploy_key"
fi

# Display public key for manual copying
echo -e "\n${GREEN}Your public SSH key:${NC}"
echo "=================================="
cat "${SSH_KEY_FILE}.pub"
echo "=================================="

# Add to server instructions
echo -e "\n${YELLOW}To manually add your key to the server:${NC}"
echo "1. Log in to your server via SSH: ssh ${SERVER_USER}@${SERVER_HOST}"
echo "2. Create the ~/.ssh directory if it doesn't exist: mkdir -p ~/.ssh"
echo "3. Add your key to authorized_keys: nano ~/.ssh/authorized_keys"
echo "4. Paste the key above into the file"
echo "5. Set correct permissions: chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys"

# Add to SSH config
if [[ -f "$SSH_CONFIG_FILE" ]]; then
    if grep -q "Host $SERVER_ALIAS" "$SSH_CONFIG_FILE"; then
        echo -e "\n${YELLOW}SSH config entry for $SERVER_ALIAS already exists${NC}"
    else
        echo -e "\n${YELLOW}Adding server to SSH config...${NC}"
        cat >> "$SSH_CONFIG_FILE" << EOF

# Siloe server
Host $SERVER_ALIAS
    HostName $SERVER_HOST
    User $SERVER_USER
    IdentityFile $SSH_KEY_FILE
EOF
        echo -e "${GREEN}Added server to SSH config successfully${NC}"
    fi
else
    echo -e "\n${YELLOW}Creating SSH config...${NC}"
    mkdir -p "$HOME/.ssh"
    cat > "$SSH_CONFIG_FILE" << EOF
# Siloe server
Host $SERVER_ALIAS
    HostName $SERVER_HOST
    User $SERVER_USER
    IdentityFile $SSH_KEY_FILE
EOF
    echo -e "${GREEN}Created SSH config successfully${NC}"
fi

# Update deploy script recommendation
echo -e "\n${YELLOW}To update your deploy script:${NC}"
echo "Edit deploy/scripts/deploy_rsync.sh and change the SSH_OPTS line to:"
echo ": \"\${SSH_OPTS:=-i $SSH_KEY_FILE}\""

echo -e "\n${GREEN}Setup complete!${NC}"
echo "After adding your key to the server, you should be able to connect using:"
echo "ssh $SERVER_ALIAS"
echo
