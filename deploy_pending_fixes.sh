#!/bin/bash

# Deploy pending fixes to production
echo "Deploying pending fixes to production..."

# Files to deploy
FILES=(
    "app/Controllers/MenuController.php"
    "app/views/menus/create.php" 
    "app/views/orders/show.php"
    "app/views/admin/dashboard.php"
)

# Deploy each file
for file in "${FILES[@]}"; do
    echo "Deploying $file..."
    scp "$file" siloecom@192.185.143.154:/home1/siloecom/siloe/"$file"
    if [ $? -eq 0 ]; then
        echo "✓ $file deployed successfully"
    else
        echo "✗ Failed to deploy $file"
    fi
done

echo "Setting permissions..."
ssh siloecom@192.185.143.154 "chmod -R 644 /home1/siloecom/siloe/app/Controllers/MenuController.php /home1/siloecom/siloe/app/views/menus/create.php /home1/siloecom/siloe/app/views/orders/show.php /home1/siloecom/siloe/app/views/admin/dashboard.php"

echo "Deployment complete!"
