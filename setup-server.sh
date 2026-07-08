#!/bin/bash
# ==============================================================
# setup-server.sh — Persiapan server untuk Bell Sekolah Otomatis
# Jalanin script ini di server sebagai root / via sudo
# ==============================================================

set -e

# ─── Konfigurasi ────────────────────────────────────────────────
SERVER_IP="${1:-$(hostname -I | awk '{print $1}')}"
PROJECT_DIR="/var/www/bell_sekolah_otomatis"

if [ "$EUID" -ne 0 ]; then
    echo "Jalankan sebagai root: sudo bash setup-server.sh [IP_SERVER]"
    exit 1
fi

echo "=== Setup Bell Sekolah Otomatis ==="
echo "Server IP : $SERVER_IP"
echo "Project   : $PROJECT_DIR"
echo ""

cd "$PROJECT_DIR" || { echo "Project dir tidak ditemukan: $PROJECT_DIR"; exit 1; }

# ─── 1. Update .env ─────────────────────────────────────────────
echo ">>> Update .env..."
[ ! -f .env ] && cp .env.example .env

sed -i "s|^APP_URL=.*|APP_URL=http://${SERVER_IP}|" .env
sed -i 's|^BROADCAST_CONNECTION=.*|BROADCAST_CONNECTION=reverb|' .env
sed -i "s|^REVERB_HOST=.*|REVERB_HOST=\"${SERVER_IP}\"|" .env
sed -i 's|^REVERB_PORT=.*|REVERB_PORT=8081|' .env
sed -i 's|^REVERB_SCHEME=.*|REVERB_SCHEME=http|' .env
sed -i 's|^REVERB_SERVER_HOST=.*|REVERB_SERVER_HOST=0.0.0.0|' .env
sed -i 's|^REVERB_SERVER_PORT=.*|REVERB_SERVER_PORT=8081|' .env
sed -i "s|^VITE_REVERB_HOST=.*|VITE_REVERB_HOST=\"${SERVER_IP}\"|" .env

# Set APP_KEY jika belum ada
grep -q 'APP_KEY=' .env || php artisan key:generate --force

# ─── 2. Systemd Service: Reverb ─────────────────────────────────
echo ">>> Buat systemd service: reverb..."
cat > /etc/systemd/system/reverb.service << 'SERVICE'
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/bell_sekolah_otomatis
ExecStart=/usr/bin/php8.4 artisan reverb:start --host=0.0.0.0 --port=8081
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
SERVICE

# ─── 3. Systemd Service: Bell Scheduler ─────────────────────────
echo ">>> Buat systemd service: bell-scheduler..."
cat > /etc/systemd/system/bell-scheduler.service << 'SERVICE'
[Unit]
Description=Laravel Schedule Worker (Bell Scheduler)
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/bell_sekolah_otomatis
ExecStart=/usr/bin/php8.4 artisan schedule:work
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

# ─── 4. Firewall ────────────────────────────────────────────────
echo ">>> Buka port 8081 di firewall..."
if command -v ufw &>/dev/null; then
    ufw allow 8081/tcp comment 'Reverb WebSocket'
elif command -v firewall-cmd &>/dev/null; then
    firewall-cmd --permanent --add-port=8081/tcp
    firewall-cmd --reload
else
    echo "  (tidak ada ufw/firewalld, lewati)"
fi

# ─── 5. Install dependencies & build ────────────────────────────
echo ">>> Install PHP dependencies..."
su -s /bin/bash -c "composer install --no-dev --optimize-autoloader" www-data

echo ">>> Install & build frontend..."
npm install --silent
npm run build

# ─── 6. Cache Laravel ──────────────────────────────────────────
echo ">>> Optimasi cache..."
su -s /bin/bash -c "php artisan config:cache && php artisan route:cache && php artisan view:cache" www-data

# ─── 7. Migration ──────────────────────────────────────────────
echo ">>> Migrasi database..."
su -s /bin/bash -c "php artisan migrate --force" www-data

# ─── 8. Set permission ─────────────────────────────────────────
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