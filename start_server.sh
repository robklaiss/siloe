#!/bin/bash

# Navigate to the project directory
cd "$(dirname "$0")"

# Start PHP built-in server
echo "Starting Siloe Lunch System server on http://127.0.0.1:8080"
php -S 127.0.0.1:8080 -t public/
