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

## Lisensi

Proyek ini open-source dan dilisensikan di bawah [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).
