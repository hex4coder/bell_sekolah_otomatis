#!/bin/bash
# ==============================================================
# setup-server.sh — Persiapan server untuk Bell Sekolah Otomatis
# Jalanin script ini di server sebagai root / via sudo
# ==============================================================

set -e

# ─── Konfigurasi ────────────────────────────────────────────────
SERVER_IP="${1:-$(hostname -I | awk '{print $1}')}"
PROJECT_DIR="/var/www/bell_sekolah_otomatis"
PHP_BIN="$(which php8.4 2>/dev/null || which php8.3 2>/dev/null || which php8.2 2>/dev/null || which php)"

if [ "$EUID" -ne 0 ]; then
    echo "Jalankan sebagai root: sudo bash setup-server.sh [IP_SERVER]"
    exit 1
fi

echo "=== Setup Bell Sekolah Otomatis ==="
echo "Server IP : $SERVER_IP"
echo "Project   : $PROJECT_DIR"
echo ""

# ─── 1. Install dependencies sistem ────────────────────────────
echo ">>> Update package list & install PHP 8.4 + extensions..."
apt update -y
apt install -y software-properties-common curl wget git unzip nginx
apt install -y php8.4 php8.4-cli php8.4-common php8.4-mbstring php8.4-xml php8.4-curl php8.4-sqlite3 php8.4-zip php8.4-bcmath php8.4-gd php8.4-intl

# Install Composer
if ! command -v composer &>/dev/null; then
    echo ">>> Install Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
fi

# Install Node.js & NPM
if ! command -v node &>/dev/null; then
    echo ">>> Install Node.js 22..."
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
    apt install -y nodejs
fi

# ─── 2. Clone / update project ─────────────────────────────────
if [ ! -d "$PROJECT_DIR" ]; then
    echo ">>> Clone project dari GitHub..."
    git clone https://github.com/hex4coder/bell_sekolah_otomatis.git "$PROJECT_DIR"
else
    echo ">>> Project sudah ada, update..."
    cd "$PROJECT_DIR"
    git pull origin main 2>/dev/null || git pull origin dev 2>/dev/null || true
fi

cd "$PROJECT_DIR" || { echo "Project dir tidak ditemukan: $PROJECT_DIR"; exit 1; }

# ─── 3. Setup SQLite database ──────────────────────────────────
echo ">>> Setup SQLite database..."
touch database/database.sqlite
chown www-data:www-data database/database.sqlite
chmod 664 database/database.sqlite

# ─── 4. Update .env ────────────────────────────────────────────
echo ">>> Update .env..."
[ ! -f .env ] && cp .env.example .env

set_env() {
    local key="$1" val="$2" file=".env"
    if grep -q "^${key}=" "$file"; then
        sed -i "s|^${key}=.*|${key}=${val}|" "$file"
    else
        echo "${key}=${val}" >> "$file"
    fi
    echo "  ${key}=${val}"
}

set_env "APP_URL"           "http://${SERVER_IP}"
set_env "BROADCAST_CONNECTION" "reverb"
set_env "REVERB_APP_ID"     "593920"
set_env "REVERB_APP_KEY"    "l6gw87idlec8pvrarutx"
set_env "REVERB_APP_SECRET" "n9fyfpat3rvcvbjndpjq"
set_env "REVERB_HOST"       "\"${SERVER_IP}\""
set_env "REVERB_PORT"       "8081"
set_env "REVERB_SCHEME"     "http"
set_env "REVERB_SERVER_HOST" "0.0.0.0"
set_env "REVERB_SERVER_PORT" "8081"
set_env "VITE_REVERB_APP_KEY" "\"l6gw87idlec8pvrarutx\""
set_env "VITE_REVERB_HOST"  "\"${SERVER_IP}\""
set_env "VITE_REVERB_PORT"  "\"8081\""
set_env "VITE_REVERB_SCHEME" "\"http\""
set_env "APP_TIMEZONE"      "Asia/Makassar"
set_env "DB_CONNECTION"     "sqlite"
set_env "DB_DATABASE"       "\"${PROJECT_DIR}/database/database.sqlite\""
set_env "SESSION_DRIVER"    "file"
set_env "CACHE_STORE"       "file"

# Set APP_KEY jika belum ada
grep -q 'APP_KEY=' .env || php artisan key:generate --force

# ─── 5. Systemd Service: Reverb ─────────────────────────────────
echo ">>> Buat systemd service: reverb..."
cat > /etc/systemd/system/reverb.service << SERVICE
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/bell_sekolah_otomatis
ExecStart=${PHP_BIN} artisan reverb:start --host=0.0.0.0 --port=8081
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
SERVICE

# ─── 6. Systemd Service: Bell Scheduler ─────────────────────────
echo ">>> Buat systemd service: bell-scheduler..."
cat > /etc/systemd/system/bell-scheduler.service << SERVICE
[Unit]
Description=Laravel Schedule Worker (Bell Scheduler)
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/bell_sekolah_otomatis
ExecStart=${PHP_BIN} artisan schedule:work
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
SERVICE

systemctl daemon-reload
systemctl enable reverb bell-scheduler
systemctl restart reverb bell-scheduler

# ─── 7. Firewall ────────────────────────────────────────────────
echo ">>> Buka port 8081 di firewall..."
if command -v ufw &>/dev/null; then
    ufw allow 8081/tcp comment 'Reverb WebSocket'
elif command -v firewall-cmd &>/dev/null; then
    firewall-cmd --permanent --add-port=8081/tcp
    firewall-cmd --reload
else
    echo "  (tidak ada ufw/firewalld, lewati)"
fi

# ─── 8. Install dependencies & build ────────────────────────────
echo ">>> Install PHP dependencies..."
su -s /bin/bash -c "composer install --no-dev --optimize-autoloader" www-data

echo ">>> Install & build frontend..."
npm install --silent
npm run build

# ─── 9. Fix packages cache ─────────────────────────────────────
# Hapus service provider dari paket dev yang sudah di-uninstall
php artisan package:discover 2>/dev/null || true
# Jika masih gagal, bersihkan packages.php secara manual
if ! php artisan package:discover 2>/dev/null; then
    echo ">>> Perbaiki packages.php (hapus dev providers)..."
    cat > bootstrap/cache/packages.php << 'PHPEOF'
<?php return array (
  "laravel/reverb" => array (
    "providers" => array (
      0 => "Laravel\\Reverb\\ApplicationManagerServiceProvider",
      1 => "Laravel\\Reverb\\ReverbServiceProvider",
    ),
  ),
  "laravel/tinker" => array (
    "providers" => array (
      0 => "Laravel\\Tinker\\TinkerServiceProvider",
    ),
  ),
  "nesbot/carbon" => array (
    "providers" => array (
      0 => "Carbon\\Laravel\\ServiceProvider",
    ),
  ),
);
PHPEOF
fi

# ─── 10. Cache Laravel ──────────────────────────────────────────
echo ">>> Optimasi cache..."
su -s /bin/bash -c "php artisan config:cache && php artisan route:cache && php artisan view:cache" www-data

# ─── 11. Migration ──────────────────────────────────────────────
echo ">>> Migrasi database..."
su -s /bin/bash -c "php artisan migrate --force" www-data

# ─── 12. Set permission ─────────────────────────────────────────
echo ">>> Set permission..."
chown -R www-data:www-data storage bootstrap/cache public/build
chmod -R 755 storage bootstrap/cache

# ─── Selesai ────────────────────────────────────────────────────
echo ""
echo "=== Setup selesai! ==="
echo "Web    : http://${SERVER_IP}"
echo "Reverb : ws://${SERVER_IP}:8081"
echo ""
echo "Cek status service:"
systemctl status reverb --no-pager -l | head -10
echo "..."
systemctl status bell-scheduler --no-pager -l | head -10