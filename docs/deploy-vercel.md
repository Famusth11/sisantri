# Deploy SISANTRI ke Vercel

Panduan ini disiapkan untuk deploy SISANTRI ke Vercel menggunakan community runtime PHP yang masih direkomendasikan di dokumentasi Vercel.

## Catatan penting

Deploy ke Vercel cocok untuk:

- demo
- uji skripsi
- preview online
- penggunaan ringan

Deploy ke Vercel kurang ideal untuk:

- workload tinggi
- proses background worker terus-menerus
- penyimpanan file lokal permanen

## Kenapa perlu penyesuaian khusus

Vercel Functions berjalan secara stateless dan filesystem runtime bersifat read-only, kecuali folder sementara `/tmp`.

Sumber:

- [Vercel runtimes](https://vercel.com/docs/functions/runtimes)
- [vercel-php](https://github.com/vercel-community/php)

Karena itu, pada deploy ini:

- Laravel dijalankan lewat `api/index.php`
- asset Vite dibuild saat proses deploy
- queue di-set ke `sync`
- compiled Blade view diarahkan ke `/tmp/views`

## File yang sudah disiapkan di repo

- `api/index.php`
- `vercel.json`
- `.vercelignore`

## Langkah deploy

1. Buka [https://vercel.com/new](https://vercel.com/new)
2. Import repository `Famusth11/sisantri`
3. Saat Vercel mendeteksi project:

- Framework Preset: `Other`
- Root Directory: `.`

4. Tambahkan environment variables berikut di dashboard Vercel

## Environment variables wajib

```env
APP_NAME=SISANTRI
APP_KEY=base64:ISI_APP_KEY_ANDA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://nama-project-anda.vercel.app

DB_CONNECTION=mysql
DB_HOST=HOST_DATABASE_ANDA
DB_PORT=3306
DB_DATABASE=sisantri
DB_USERNAME=USERNAME_DATABASE
DB_PASSWORD=PASSWORD_DATABASE

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
VIEW_COMPILED_PATH=/tmp/views
```

## Environment variables opsional

Jika fitur Google Sheets dipakai:

```env
GOOGLE_SHEETS_ID=...
GOOGLE_SHEETS_CREDENTIALS_PATH=
```

Catatan:

- `GOOGLE_SHEETS_CREDENTIALS_PATH` berbasis file lokal tidak praktis di Vercel
- kalau fitur sinkronisasi Google Sheets tidak dipakai di deployment ini, biarkan kosong

Untuk email:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=SISANTRI
```

## Database

Vercel tidak menyediakan MySQL lokal bawaan. Anda tetap perlu database eksternal, misalnya:

- MySQL hosting kampus
- Railway MySQL
- PlanetScale
- Aiven
- VPS database sendiri

Jika database belum berisi struktur tabel, jalankan migrasi dari lokal atau server lain yang terhubung ke database itu:

```bash
php artisan migrate --force
```

Atau import dari dump `.sql`.

## Generate APP_KEY

Kalau belum punya `APP_KEY`, dari lokal jalankan:

```bash
php artisan key:generate --show
```

Lalu copy hasilnya ke environment variable `APP_KEY` di Vercel.

## Setelah deploy

Uji fitur berikut:

- login
- dashboard
- presensi sholat
- presensi diniyah
- rekap bulanan
- export PDF/Excel

## Keterbatasan deploy Vercel untuk SISANTRI

- tidak cocok untuk queue worker jangka panjang
- tidak cocok untuk storage file lokal permanen
- `storage:link` tidak berfungsi seperti di VPS tradisional
- cold start bisa terasa dibanding VPS

## Rekomendasi

Jika tujuan Anda:

- demo skripsi: Vercel cukup layak
- sistem operasional harian pondok: lebih baik VPS
