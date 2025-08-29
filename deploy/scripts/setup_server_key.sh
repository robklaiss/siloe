#!/bin/bash
# Script to set up SSH key on the server side

# Colors for output
GREEN="\033[0;32m"
YELLOW="\033[0;33m"
NC="\033[0m" # No Color

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY=$(cat ~/.ssh/siloe_ed25519.pub)

echo -e "${GREEN}Setting up SSH key on the server...${NC}"

# Create the script to run on the server
cat > /tmp/setup_key.sh << EOF
#!/bin/bash
# Ensure .ssh directory exists with correct permissions
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Add the key to authorized_keys
echo "$SSH_KEY" >> ~/.ssh/authorized_keys

# Set correct permissions
chmod 600 ~/.ssh/authorized_keys

# Output results
echo "Key added to authorized_keys"
echo "Directory permissions:"
ls -la ~/.ssh
echo "Content of authorized_keys:"
cat ~/.ssh/authorized_keys
EOF

# Copy the script to the server and execute it
scp /tmp/setup_key.sh ${SERVER_USER}@${SERVER_HOST}:/tmp/
ssh ${SERVER_USER}@${SERVER_HOST} "chmod +x /tmp/setup_key.sh && /tmp/setup_key.sh"

# Clean up
rm /tmp/setup_key.sh

echo -e "${GREEN}Setup complete! Testing connection...${NC}"
echo "ssh ${SERVER_USER}@${SERVER_HOST} echo 'SSH key working!'"
