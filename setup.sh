#!/bin/bash
set -e

SOURCE="/home/pmsd/.gemini/antigravity/scratch/xodimlar"
DEST="$HOME/xodimlar_run"

echo "=========================================="
echo "   XODIMLAR AUTOMATED SETUP STARTING"
echo "=========================================="

# 1. Update run directory
echo "[1/5] Updating project files in $DEST..."
if [ -d "$DEST" ]; then
    echo "removing old directory..."
    sudo rm -rf "$DEST"
fi
sudo cp -r "$SOURCE" "$DEST"
# Fix permissions so you own the files, not root
sudo chown -R $USER:$USER "$DEST"

cd "$DEST"

# 2. Docker Restart
echo "[2/5] Restarting Docker Containers..."
sudo docker compose down --remove-orphans
sudo docker compose up -d --build

echo "Waiting 15 seconds for Database to initialize..."
sleep 15

# 3. Dependencies
echo "[3/5] Installing Dependencies inside Container..."
sudo docker compose exec -T app composer install
sudo docker compose exec -T app npm install
sudo docker compose exec -T app npm run build

# 4. Database Setup
echo "[4/5] Setting up Database..."
# Reset DB to be sure
sudo docker compose exec -T app php bin/console doctrine:database:drop --force --if-exists || true
sudo docker compose exec -T app php bin/console doctrine:database:create --if-not-exists
sudo docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
sudo docker compose exec -T app php bin/console doctrine:fixtures:load --no-interaction

echo "=========================================="
echo "   SUCCESS! SYSTEM IS READY"
echo "=========================================="
echo "Open: http://localhost:8000"
echo "CEO Login: +998901234567 / password123"
