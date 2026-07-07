cd /var/www/bell_sekolah_otomatis
chmod +x /var/www/bell_sekolah_otomatis/updater.sh
            
git stash
git clean -df
git pull

# 1. Reset file database SQLite agar kosong kembali
> /var/www/bell_sekolah_otomatis/database/database.sqlite

# 2. Jalankan rangkaian perintah andalan Anda
su -s /bin/bash -c "composer install --no-dev" www-data
su -s /bin/bash -c "php artisan key:generate" www-data
su -s /bin/bash -c "php artisan storage:link" www-data
su -s /bin/bash -c "php artisan migrate --seed" www-data
su -s /bin/bash -c "php artisan config:cache && php artisan route:cache && php artisan view:cache" www-data
