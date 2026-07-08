# Bell Sekolah Otomatis

Sistem penjadwalan bell sekolah otomatis berbasis web. Dibangun dengan **Laravel 13**, **Tailwind CSS**, **SQLite**, dan **Vite**. Dilengkapi panel admin untuk mengelola jadwal bell, audio assets, hari sekolah aktif, serta fitur bell darurat.

## Fitur

- **Jadwal Bell Otomatis** — Atur jadwal per hari (Senin–Sabtu) dengan waktu dan file audio masing-masing.
- **Manajemen Audio** — Upload, edit nama, dan hapus file audio (MP3, WAV, OGG). File disimpan di `assets_audio/`.
- **Hari Sekolah** — Aktif/nonaktifkan hari tertentu. Jadwal hanya tampil di halaman publik pada hari aktif.
- **Copy Jadwal** — Salin jadwal dari satu hari ke hari lain.
- **Bell Darurat** — Tombol di dashboard admin untuk memutar bell darurat. Otomatis mendeteksi jadwal bell pulang (nama mengandung "akhir") jika tidak ada audio yang dipilih. Audio dikirim ke halaman welcome via polling.
- **Dark Mode** — Toggle dark/light mode dengan penyimpanan preferensi di `localStorage`.
- **Multi User** — Registrasi user biasa; hanya admin (`is_admin = true`) yang bisa mengakses panel admin.
- **Halaman Publik** — Welcome page menampilkan jadwal bell hari ini.

## Persyaratan Sistem

- PHP ^8.3
- Composer
- Node.js & npm
- SQLite (sudah termasuk di PHP)
- Web server (Apache/Nginx) atau built-in server Laravel

## Instalasi

### 1. Clone repositori

```bash
git clone https://github.com/username/bell_sekolah_otomatis.git
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
APP_URL=https://domain-anda.com

DB_CONNECTION=sqlite
# SQLite: file database akan otomatis dibuat di database/database.sqlite
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

### 8. Setup scheduler (WAJIB untuk bell otomatis)

Laravel scheduler harus jalan setiap menit. Tambahkan cron job di server:

```bash
crontab -e
```

Lalu tambahkan baris berikut:

```
* * * * * cd /path/to/bell_sekolah_otomatis && php artisan schedule:run >> /dev/null 2>&1
```

> **Catatan:** Project ini menggunakan `QUEUE_CONNECTION=database` dan `CACHE_STORE=database`. Pastikan migration sudah dijalankan agar tabel queue dan cache tersedia.

### 9. Queue worker (jika menggunakan antrian)

Jika ada job yang di-queue, jalankan worker:

```bash
php artisan queue:work --daemon &
```

### 10. Hak akses direktori

```bash
chmod -R 775 storage bootstrap/cache assets_audio
chown -R www-data:www-data storage bootstrap/cache assets_audio   # untuk Nginx/Apache
```

## Deployment dengan Web Server

### Nginx

```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /path/to/bell_sekolah_otomatis/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
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

## Struktur Direktori

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AdminController.php    # Semua logic panel admin
│   │   └── Middleware/
│   │       └── AdminMiddleware.php     # Middleware akses admin
│   └── Models/
│       ├── AudioAsset.php
│       ├── BellSchedule.php
│       ├── SchoolDay.php
│       └── User.php
├── assets_audio/                       # Folder penyimpanan file audio
├── database/
│   ├── migrations/                     # Migrasi tabel
│   └── database.sqlite                 # Database SQLite
├── resources/
│   └── views/
│       ├── admin/                      # View panel admin
│       │   ├── audio/
│       │   ├── schedules/
│       │   ├── dashboard.blade.php
│       │   └── school-days.blade.php
│       ├── layouts/
│       │   └── app.blade.php
│       └── welcome.blade.php           # Halaman publik
├── routes/
│   └── web.php                         # Semua route
└── tailwind.config.js                  # Konfigurasi Tailwind (dark mode class)
```

## Route API

| Method | URL                            | Deskripsi                              |
|--------|--------------------------------|----------------------------------------|
| GET    | `/`                            | Halaman publik jadwal hari ini         |
| GET    | `/audio/{filename}`            | Serve file audio                       |
| GET    | `/api/emergency-bell`          | Polling bell darurat (dipanggil JS)    |
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

## Maintenance

### Backup database

```bash
cp database/database.sqlite database/backup-$(date +%Y%m%d).sqlite
```

### Clear cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Konfigurasi Client (Browser)

Aplikasi ini menggunakan WebSocket (Laravel Reverb) untuk memicu suara bel secara real-time. Browser client harus diizinkan memutar audio otomatis (autoplay) dan tidak boleh menidurkan tab. Berikut panduan untuk Firefox dan Chrome.

### Firefox

#### Langkah 1: Bypass Aturan Autoplay

Firefox memiliki pengamanan autoplay yang cukup ketat. Agar browser mengizinkan suara bel berbunyi otomatis tanpa perlu diklik terlebih dahulu, lakukan pengaturan ini sekali saja di PC Klien:

1. Buka Firefox di PC Klien.
2. Di kolom alamat (address bar), ketik `about:config` lalu tekan Enter.
3. Jika muncul peringatan keamanan, klik **Accept the Risk and Continue**.
4. Di kolom pencarian atas, cari: `media.autoplay.default`
5. Ubah nilainya dari `5` (atau `1`) menjadi `0` (Angka `0` artinya Allow Autoplay).
6. Selanjutnya, cari: `media.autoplay.blocking_policy`
7. Ubah nilainya menjadi `0`.
8. Tutup Firefox.

#### Langkah 2: Matikan Tab Unloading

Firefox memiliki fitur `Tab Unloading` yang otomatis menidurkan tab pasif untuk menghemat RAM. Matikan agar koneksi WebSocket tidak terputus.

1. Buka kembali `about:config` di Firefox.
2. Cari: `browser.tabs.unloadOnLowMemory`
3. Ubah nilainya menjadi `false` (klik dua kali).

#### Langkah 3: Auto-Start Firefox Kiosk Mode

**Windows:** Buat file `mulai-bel.bat`:

```batch
@echo off
title Menjalankan Bel Sekolah Firefox Kiosk Mode
echo Mengaktifkan sistem bel sekolah...
timeout /t 5
start "" "C:\Program Files\Mozilla Firefox\firefox.exe" --kiosk "http://IP_SERVER_ANDA"
exit
```

**Linux (Ubuntu/Debian):** Buat file `mulai-bel.sh`:

```bash
#!/bin/bash
sleep 5
firefox --kiosk "http://IP_SERVER_ANDA"
```

> **Catatan:** Mode `--kiosk` membuka Firefox satu layar penuh tanpa address bar atau tombol close.

### Google Chrome

#### Langkah 1: Bypass Aturan Autoplay

Chrome juga memblokir autoplay audio. Untuk mengizinkannya, akses halaman bel sekolah terlebih dahulu, lalu klik ikon gembok di address bar → **Site Settings** → **Sound** → ubah menjadi **Allow**.

Cara alternatif melalui pengaturan global:
1. Buka `chrome://settings/content/sound` di address bar.
2. Ubah pengaturan menjadi **Allow sites to play sound**.

#### Langkah 2: Matikan Memory Saver

Chrome memiliki fitur **Memory Saver** yang bisa menidurkan tab agar koneksi WebSocket terputus.

1. Buka `chrome://settings/performance`.
2. Matikan **Memory Saver** (toggle ke off).

#### Langkah 3: Auto-Start Chrome Kiosk Mode

**Windows:** Buat file `mulai-bel.bat`:

```batch
@echo off
title Menjalankan Bel Sekolah Chrome Kiosk Mode
echo Mengaktifkan sistem bel sekolah...
timeout /t 5
start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk "http://IP_SERVER_ANDA"
exit
```

**Linux (Ubuntu/Debian):** Buat file `mulai-bel.sh`:

```bash
#!/bin/bash
sleep 5
google-chrome --kiosk "http://IP_SERVER_ANDA"
```

## Lisensi

Proyek ini open-source dan dilisensikan di bawah [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).
