#!/bin/bash
# Script to set up SSH config for siloe server

SSH_CONFIG_FILE="$HOME/.ssh/config"

# Create or update SSH config
if grep -q "Host siloe" "$SSH_CONFIG_FILE" 2>/dev/null; then
  echo "SSH config entry already exists. Updating..."
  # Use sed to update existing entry
  sed -i '' '/Host siloe/,/^$/ s/IdentityFile.*/IdentityFile ~\/.ssh\/id_rsa/' "$SSH_CONFIG_FILE"
else
  echo "Creating new SSH config entry..."
  mkdir -p "$HOME/.ssh"
  cat >> "$SSH_CONFIG_FILE" << EOF

# Siloe server
Host siloe
    HostName 192.185.143.154
    User siloecom
    IdentityFile ~/.ssh/id_rsa
    IdentitiesOnly yes
EOF
fi

echo "SSH config updated. You can now use: ssh siloe"
echo "Config file contents:"
cat "$SSH_CONFIG_FILE" | grep -A5 "Host siloe"
