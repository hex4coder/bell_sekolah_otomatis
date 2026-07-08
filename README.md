# Bell Sekolah Otomatis

Sistem penjadwalan bell sekolah otomatis berbasis web. Dibangun dengan **Laravel 13**, **Tailwind CSS**, **SQLite**, dan **Vite**. Dilengkapi panel admin untuk mengelola jadwal bell, audio assets, hari sekolah aktif, serta fitur bell darurat.

## Fitur

- **Jadwal Bell Otomatis** — Atur jadwal per hari (Senin–Sabtu) dengan waktu dan file audio masing-masing.
- **Push Realtime via WebSocket** — Menggunakan **Laravel Reverb** (Pusher protocol). Bell bunyi otomatis di semua client tanpa perlu refresh halaman.
- **Scheduler Server-side** — Command `app:process-bell-schedules` berjalan tiap menit via Laravel Scheduler. Broadcast `BellPlayed` event ke semua client yang terhubung via Reverb.
- **Manajemen Audio** — Upload, edit nama, dan hapus file audio (MP3, WAV, OGG). File disimpan di `assets_audio/`.
- **Hari Sekolah** — Aktif/nonaktifkan hari tertentu. Jadwal hanya tampil di halaman publik pada hari aktif.
- **Copy Jadwal** — Salin jadwal dari satu hari ke hari lain.
- **Bell Darurat** — Tombol di dashboard admin untuk memutar bell darurat. Otomatis mendeteksi jadwal bell pulang (nama mengandung "akhir") jika tidak ada audio yang dipilih. Audio dikirim ke client via WebSocket realtime.
- **Dark Mode** — Toggle dark/light mode dengan penyimpanan preferensi di `localStorage`.
- **Multi User** — Registrasi user biasa; hanya admin (`is_admin = true`) yang bisa mengakses panel admin.
- **Halaman Publik** — Welcome page menampilkan jadwal bell hari ini.

## Persyaratan Sistem

- PHP ^8.2 (8.4 recommended)
- Composer
- Node.js & npm
- SQLite (sudah termasuk di PHP)
- Web server (Apache/Nginx)
- Ekstensi PHP: `pdo_sqlite`, `mbstring`, `xml`, `curl`, `fileinfo`, `bcmath`, `json`, `openssl`, `sockets`, `pcntl`

## Instalasi Cepat (Native Linux Server)

Untuk server native (Apache/Nginx tanpa Docker), gunakan script `setup-server.sh`:

```bash
# Clone repositori
git clone https://github.com/hex4coder/bell_sekolah_otomatis.git /var/www/bell_sekolah_otomatis
cd /var/www/bell_sekolah_otomatis

# Jalankan setup (sebagai root / sudo)
sudo bash setup-server.sh IP_SERVER_ANDA
```

Script akan otomatis:
1. Update `.env` dengan IP server
2. Buat & start **systemd service** `reverb` (WebSocket) dan `bell-scheduler` (scheduler tiap menit)
3. Buka port `8081` di firewall untuk WebSocket
4. Install dependency PHP & frontend, build Vite
5. Jalankan migrasi database
6. Set permission direktori

## Instalasi Manual

### 1. Clone repositori

```bash
git clone https://github.com/hex4coder/bell_sekolah_otomatis.git
cd bell_sekolah_otomatis
```

### 2. Install dependency PHP

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Install dependency frontend

```bash
npm install
npm run build
```

### 4. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan `.env` untuk production:

```env
APP_NAME="Bell Sekolah"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://IP_SERVER_ANDA

DB_CONNECTION=sqlite

BROADCAST_CONNECTION=reverb
REVERB_HOST="IP_SERVER_ANDA"
REVERB_PORT=8081
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8081
```

### 5. Buat database SQLite

```bash
touch database/database.sqlite
php artisan migrate --force
```

### 6. Setup direktori audio

```bash
mkdir -p assets_audio
```

Letakkan file audio (MP3/WAV/OGG) di `assets_audio/`, lalu daftarkan lewat panel admin.

### 7. Buat user admin

```bash
php artisan tinker --execute="
\$user = new App\Models\User();
\$user->name = 'Admin';
\$user->email = 'admin@example.com';
\$user->password = bcrypt('password123');
\$user->is_admin = true;
\$user->save();
"
```

Ubah email dan password sesuai kebutuhan.

### 8. Setup Reverb WebSocket (WAJIB)

Reverb harus berjalan sebagai daemon. Ada dua cara:

**a. Systemd service (recommended)**

```bash
# Buat file /etc/systemd/system/reverb.service
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/bell_sekolah_otomatis
ExecStart=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=8081
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
systemctl daemon-reload
systemctl enable --now reverb
```

**b. Manual (nohup)**

```bash
nohup php artisan reverb:start --host=0.0.0.0 --port=8081 > /dev/null 2>&1 &
```

Pastikan port `8081` terbuka di firewall:

```bash
sudo ufw allow 8081/tcp
```

### 9. Setup Scheduler (WAJIB untuk bell otomatis)

Scheduler berfungsi menjalankan `app:process-bell-schedules` setiap menit untuk mengecek dan memicu jadwal bell.

**a. Systemd service (recommended)**

```bash
# Buat file /etc/systemd/system/bell-scheduler.service
[Unit]
Description=Laravel Schedule Worker (Bell Scheduler)
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/bell_sekolah_otomatis
ExecStart=/usr/bin/php artisan schedule:work
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
systemctl daemon-reload
systemctl enable --now bell-scheduler
```

**b. Cron job (alternatif)**

```bash
crontab -e
```

Lalu tambahkan:

```
* * * * * cd /var/www/bell_sekolah_otomatis && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Hak akses direktori

```bash
chmod -R 775 storage bootstrap/cache assets_audio public/build
chown -R www-data:www-data storage bootstrap/cache assets_audio public/build
```

## Deployment dengan Web Server

### Nginx

```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/bell_sekolah_otomatis/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Apache

Pastikan mod_rewrite aktif, lalu upload project ke server. File `.htaccess` di folder `public/` sudah siap.

## Arsitektur WebSocket & Scheduler

```
┌─────────────┐     ┌──────────────┐     ┌────────────────┐
│  Admin Panel │────▶│ BellPlayed / │────▶│  Laravel Reverb │
│  (CRUD/Save) │     │ Scheduler    │     │  (WebSocket)   │
└─────────────┘     └──────────────┘     └───────┬────────┘
                                                 │
                                                 ▼
                                          ┌────────────────┐
                                          │  Landing Page  │
                                          │  (Echo Client) │
                                          └────────────────┘

Flow:
1. Scheduler (app:process-bell-schedules) jalan tiap menit
2. Cek jadwal hari ini yang waktunya == menit sekarang (±2 menit grace period)
3. Broadcast BellPlayed event via Reverb ke channel "bell"
4. Semua client yang connect menerima event, bell bunyi otomatis
```

### Events

| Event                   | Trigger                              | Channel | Payload                              |
|-------------------------|--------------------------------------|---------|--------------------------------------|
| `BellPlayed`            | Scheduler setiap menit               | `bell`  | `id`, `name`, `time`, `audio_file`   |
| `EmergencyBellTriggered`| Admin → Bell Darurat                 | `bell`  | `audio_file`                         |
| `ScheduleUpdated`       | Admin CRUD jadwal                    | `bell`  | (tidak ada payload, client reload)   |

## Route API

| Method | URL                            | Deskripsi                              |
|--------|--------------------------------|----------------------------------------|
| GET    | `/`                            | Halaman publik jadwal hari ini         |
| GET    | `/audio/{filename}`            | Serve file audio                       |
| GET    | `/api/emergency-bell`          | (deprecated, diganti WebSocket)        |
| POST   | `/admin/bell-darurat`          | Trigger bell darurat                   |
| POST   | `/admin/schedules`             | Tambah jadwal                          |
| PUT    | `/admin/schedules/{id}`        | Update jadwal                          |
| DELETE | `/admin/schedules/{id}`        | Hapus jadwal                           |
| DELETE | `/admin/schedules/reset`       | Hapus semua jadwal                     |
| DELETE | `/admin/schedules/day/{day}`   | Hapus jadwal per hari                  |
| POST   | `/admin/schedules/copy`        | Copy jadwal antar hari                 |
| POST   | `/admin/audio/upload`          | Upload file audio                      |
| DELETE | `/admin/audio/{filename}`      | Hapus audio                            |
| PUT    | `/admin/audio/{filename}/edit` | Edit nama audio                        |
| GET    | `/admin/school-days`           | Lihat pengaturan hari sekolah          |
| PUT    | `/admin/school-days`           | Update hari sekolah aktif              |

## Struktur Direktori

```
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── ProcessBellSchedules.php   # Scheduler bell tiap menit
│   ├── Events/
│   │   ├── BellPlayed.php                 # Event bell normal
│   │   ├── EmergencyBellTriggered.php     # Event bell darurat
│   │   └── ScheduleUpdated.php            # Event update jadwal
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AdminController.php        # Semua logic panel admin
│   │   └── Middleware/
│   │       └── AdminMiddleware.php        # Middleware akses admin
│   └── Models/
│       ├── AudioAsset.php
│       ├── BellSchedule.php
│       ├── SchoolDay.php
│       └── User.php
├── assets_audio/                          # Folder penyimpanan file audio
├── config/
│   ├── broadcasting.php                   # Config Reverb/Pusher
│   └── reverb.php                         # Config Reverb server
├── database/
│   ├── migrations/
│   └── database.sqlite
├── docker/
│   └── 8.3-alpine/
│       ├── Dockerfile
│       ├── supervisord.conf               # Supervisor untuk Sail (Reverb + Scheduler)
│       └── start-container
├── resources/
│   ├── js/
│   │   ├── app.js                         # Entry point Vite
│   │   └── bootstrap.js                   # Setup Echo + WebSocket listeners
│   └── views/
│       ├── admin/
│       ├── layouts/
│       └── welcome.blade.php              # Halaman publik
├── routes/
│   ├── channels.php                       # Channel broadcast (bell)
│   ├── console.php                        # Jadwal scheduler
│   └── web.php                            # Semua route HTTP
├── setup-server.sh                        # Script setup otomatis server native
├── docker-compose.yml                     # Konfigurasi Sail (development)
└── vite.config.js
```

## Maintenance

### Backup database

```bash
cp database/database.sqlite database/backup-$(date +%Y%m%d).sqlite
```

### Cek status service (systemd)

```bash
systemctl status reverb
systemctl status bell-scheduler
```

### Restart service

```bash
systemctl restart reverb bell-scheduler
```

### Clear cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Cek log WebSocket

```bash
journalctl -u reverb -f --no-pager
journalctl -u bell-scheduler -f --no-pager
```

## Konfigurasi Client (Browser)

Aplikasi ini menggunakan WebSocket (Laravel Reverb) untuk memicu suara bel secara real-time. Browser client harus diizinkan memutar audio otomatis (autoplay) dan tidak boleh menidurkan tab. Berikut panduan untuk Firefox dan Chrome.

### Firefox

#### Langkah 1: Bypass Aturan Autoplay

1. Buka `about:config` di address bar.
2. Cari: `media.autoplay.default` → ubah jadi `0` (Allow).
3. Cari: `media.autoplay.blocking_policy` → ubah jadi `0`.

#### Langkah 2: Matikan Tab Unloading

1. Buka `about:config`.
2. Cari: `browser.tabs.unloadOnLowMemory` → ubah jadi `false`.

#### Langkah 3: Auto-Start Firefox Kiosk Mode

**Windows:** Buat file `mulai-bel.bat`:
```batch
@echo off
start "" "C:\Program Files\Mozilla Firefox\firefox.exe" --kiosk "http://IP_SERVER_ANDA"
```

**Linux:** Buat file `mulai-bel.sh`:
```bash
#!/bin/bash
sleep 5
firefox --kiosk "http://IP_SERVER_ANDA"
```

### Google Chrome

#### Langkah 1: Bypass Aturan Autoplay

Akses halaman bel, klik gembok di address bar → **Site Settings** → **Sound** → **Allow**.

#### Langkah 2: Matikan Memory Saver

1. Buka `chrome://settings/performance`.
2. Matikan **Memory Saver**.

#### Langkah 3: Auto-Start Chrome Kiosk Mode

**Windows:**
```batch
@echo off
start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk "http://IP_SERVER_ANDA"
```

**Linux:**
```bash
#!/bin/bash
sleep 5
google-chrome --kiosk "http://IP_SERVER_ANDA"
```

## Lisensi

Proyek ini open-source dan dilisensikan di bawah [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).