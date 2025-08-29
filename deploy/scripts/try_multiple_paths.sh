#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"
LOCAL_FILE="/Users/robinklaiss/Dev/siloe/fix_auth_system.php"

# Common paths in cPanel environments
PATHS=(
  "/home/siloecom/public_html"
  "/home/siloecom/www"
  "/home/siloecom/htdocs"
  "/home/siloecom/web"
  "/home/siloecom/siloe/public"
  "/home/siloecom/siloe"
  "/home/siloecom/public_html/siloe"
)

echo "Trying to upload auth fix to multiple possible paths..."

for path in "${PATHS[@]}"; do
  echo "Trying path: $path"
  
  # Try to create directory (will fail silently if it exists or if no permission)
  ssh -o PreferredAuthentications=password $SERVER "mkdir -p $path" 2>/dev/null
  
  # Try to upload the file
  scp -o PreferredAuthentications=password $LOCAL_FILE $SERVER:$path/ 2>/dev/null
  
  # Check if upload was successful
  if [ $? -eq 0 ]; then
    echo "SUCCESS: Uploaded to $path"
    ssh -o PreferredAuthentications=password $SERVER "chmod 644 $path/fix_auth_system.php" 2>/dev/null
    echo "Set permissions on $path/fix_auth_system.php"
  else
    echo "FAILED: Could not upload to $path"
  fi
done

echo "Upload attempts complete. Please check the following URLs:"
echo "http://www.siloe.com.py/fix_auth_system.php"
echo "http://www.siloe.com.py/siloe/fix_auth_system.php"
