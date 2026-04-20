# Deploy SISANTRI ke VPS Ubuntu

Panduan ini disiapkan untuk deploy aplikasi SISANTRI ke VPS Ubuntu dengan stack:

- Ubuntu 22.04/24.04
- Nginx
- PHP 8.2 FPM
- MySQL / MariaDB
- Node.js
- Supervisor
- SSL dari Let's Encrypt

## 1. Data yang perlu disiapkan

Sebelum deploy, siapkan:

- IP VPS
- domain atau subdomain yang mengarah ke VPS
- akses `sudo` di VPS
- database MySQL
- file `.env` production
- file kredensial Google Sheets jika fitur sinkronisasi dipakai

## 2. Install paket server

Masuk ke VPS:

```bash
ssh root@IP_VPS
```

Update paket:

```bash
sudo apt update && sudo apt upgrade -y
```

Install dependency utama:

```bash
sudo apt install -y nginx mysql-server unzip git supervisor curl software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-intl
sudo apt install -y composer
```

Install Node.js:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

## 3. Buat folder aplikasi

Contoh lokasi deploy:

```bash
sudo mkdir -p /var/www/sisantri
sudo chown -R $USER:$USER /var/www/sisantri
cd /var/www/sisantri
```

Clone repository:

```bash
git clone https://github.com/Famusth11/sisantri.git .
```

## 4. Siapkan database

Masuk ke MySQL:

```bash
sudo mysql
```

Lalu buat database dan user:

```sql
CREATE DATABASE sisantri CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sisantri_user'@'localhost' IDENTIFIED BY 'PASSWORD_STRONG';
GRANT ALL PRIVILEGES ON sisantri.* TO 'sisantri_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Jika Anda sudah punya dump `.sql`, import setelah database dibuat:

```bash
mysql -u sisantri_user -p sisantri < sisantri.sql
```

## 5. Siapkan environment

Salin contoh environment:

```bash
cp deploy/.env.production.example .env
```

Edit:

```bash
nano .env
```

Yang wajib dicek:

- `APP_NAME`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- `DB_*`
- `MAIL_*`
- `GOOGLE_SHEETS_ID`
- `GOOGLE_SHEETS_CREDENTIALS_PATH`

Generate key jika belum diisi:

```bash
php artisan key:generate
```

## 6. Install dependency aplikasi

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## 7. Jalankan setup Laravel

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
```

Jika aplikasi menggunakan queue database, pastikan tabel job sudah ada dari migrasi bawaan.

## 8. Permission folder

```bash
sudo chown -R www-data:www-data /var/www/sisantri
sudo find /var/www/sisantri -type f -exec chmod 644 {} \;
sudo find /var/www/sisantri -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/sisantri/storage
sudo chmod -R 775 /var/www/sisantri/bootstrap/cache
```

## 9. Konfigurasi Nginx

Salin contoh konfigurasi:

```bash
sudo cp deploy/nginx/sisantri.conf.example /etc/nginx/sites-available/sisantri
```

Edit domain:

```bash
sudo nano /etc/nginx/sites-available/sisantri
```

Aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/sisantri /etc/nginx/sites-enabled/sisantri
sudo nginx -t
sudo systemctl restart nginx
```

Pastikan PHP-FPM aktif:

```bash
sudo systemctl enable php8.2-fpm
sudo systemctl restart php8.2-fpm
```

## 10. SSL HTTPS

Install Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
```

Generate SSL:

```bash
sudo certbot --nginx -d domainanda.com -d www.domainanda.com
```

## 11. Supervisor untuk queue

Jika Anda tetap memakai:

```env
QUEUE_CONNECTION=database
```

buat worker dengan Supervisor:

```bash
sudo cp deploy/supervisor/sisantri-worker.conf.example /etc/supervisor/conf.d/sisantri-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sisantri-worker:*
```

Jika tidak menggunakan queue worker, Anda bisa ubah `.env` menjadi:

```env
QUEUE_CONNECTION=sync
```

## 12. Verifikasi deploy

Cek service:

```bash
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo supervisorctl status
```

Cek aplikasi:

- buka domain di browser
- login admin
- tes halaman dashboard
- tes presensi sholat
- tes presensi diniyah
- tes rekap

## 13. Update aplikasi setelah deploy

Setiap ada perubahan baru dari GitHub:

```bash
cd /var/www/sisantri
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart sisantri-worker:*
```

## 14. File penting

- Root project: `/var/www/sisantri`
- Public web root: `/var/www/sisantri/public`
- Log Laravel: `/var/www/sisantri/storage/logs/laravel.log`
- Nginx site config: `/etc/nginx/sites-available/sisantri`
- Supervisor config: `/etc/supervisor/conf.d/sisantri-worker.conf`

## 15. Catatan untuk SISANTRI

- Aplikasi ini memakai `storage:link`
- Driver cache, queue, dan session dapat memakai database
- Email default masih bisa memakai `log` jika belum pakai SMTP sungguhan
- Fitur sinkronisasi Google Sheets membutuhkan file credential JSON di server
