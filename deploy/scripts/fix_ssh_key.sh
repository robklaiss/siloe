#!/bin/bash
# Script to fix SSH key authentication on the server

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"

# Check if SSH key exists, if not, create one
SSH_KEY="$HOME/.ssh/id_rsa"
SSH_KEY_PUB="${SSH_KEY}.pub"

if [ ! -f "$SSH_KEY_PUB" ]; then
    echo "SSH key not found. Creating a new one..."
    ssh-keygen -t rsa -b 4096 -f "$SSH_KEY" -N "" -C "siloe_deployment_key"
fi

# Get the public key content
PUB_KEY=$(cat "$SSH_KEY_PUB")

echo "=== SSH Key Authentication Fix ==="
echo "This script will fix SSH authentication issues by:"
echo "1. Creating a temporary SSH config to avoid too many authentication attempts"
echo "2. Setting up your SSH key on the server"
echo "3. Testing the connection"
echo

# Create a temporary SSH config to avoid too many authentication failures
SSH_CONFIG_FILE="/tmp/siloe_ssh_config"
cat > "$SSH_CONFIG_FILE" << EOF
Host siloe-server
    HostName $SERVER_HOST
    User $SERVER_USER
    IdentityFile $SSH_KEY
    PubkeyAuthentication yes
    PasswordAuthentication yes
    PreferredAuthentications publickey,password
    StrictHostKeyChecking no
    ConnectTimeout 30
    ServerAliveInterval 60
    ServerAliveCountMax 30
EOF

echo "Created temporary SSH config at $SSH_CONFIG_FILE"

# Create a temporary script to run on the server
cat > /tmp/setup_key.sh << 'EOF'
#!/bin/bash

# Create .ssh directory if it doesn't exist
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Backup existing authorized_keys
if [ -f ~/.ssh/authorized_keys ]; then
    cp ~/.ssh/authorized_keys ~/.ssh/authorized_keys.bak
fi

# Add the public key to authorized_keys (avoid duplicates)
grep -q "$1" ~/.ssh/authorized_keys 2>/dev/null || echo "$1" >> ~/.ssh/authorized_keys

# Fix permissions
chmod 600 ~/.ssh/authorized_keys

# Verify the setup
echo "SSH directory contents:"
ls -la ~/.ssh/
echo "Authorized keys (last few lines):"
tail -5 ~/.ssh/authorized_keys

echo "SSH setup complete!"
EOF

# Make the script executable
chmod +x /tmp/setup_key.sh

# Copy the script to the server and run it using the temporary SSH config
echo "Copying setup script to server..."
scp -F "$SSH_CONFIG_FILE" /tmp/setup_key.sh siloe-server:/tmp/

echo "Running setup script on server..."
ssh -F "$SSH_CONFIG_FILE" siloe-server "/tmp/setup_key.sh '$PUB_KEY'"

# Test the connection
echo "Testing SSH connection..."
ssh -F "$SSH_CONFIG_FILE" siloe-server "echo 'SSH connection successful!'"

# Clean up
echo "Cleaning up..."
rm /tmp/setup_key.sh
ssh -F "$SSH_CONFIG_FILE" siloe-server "rm /tmp/setup_key.sh"

# Create a permanent SSH config entry
USER_SSH_CONFIG="$HOME/.ssh/config"
if [ ! -f "$USER_SSH_CONFIG" ]; then
    touch "$USER_SSH_CONFIG"
    chmod 600 "$USER_SSH_CONFIG"
fi

# Check if the host is already in the config
if ! grep -q "Host siloe-server" "$USER_SSH_CONFIG"; then
    echo "Adding siloe-server to your SSH config..."
    cat >> "$USER_SSH_CONFIG" << EOF

# Siloe server configuration
Host siloe-server
    HostName $SERVER_HOST
    User $SERVER_USER
    IdentityFile $SSH_KEY
    PreferredAuthentications publickey,password
    ServerAliveInterval 60
    ServerAliveCountMax 30
EOF
    echo "Added siloe-server to your SSH config at $USER_SSH_CONFIG"
fi

echo "Done! You can now connect with: ssh siloe-server"
