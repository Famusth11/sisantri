# SISANTRI

SISANTRI adalah sistem informasi santri berbasis Laravel yang digunakan untuk pengelolaan data santri, presensi sholat, presensi diniyah/tahfidz, rekap bulanan, dan manajemen pengguna berdasarkan hak akses.

## Fitur Utama

- Presensi sholat dengan input manual dan dukungan QR
- Presensi diniyah dan tahfidz berdasarkan jadwal aktif
- Rekap bulanan presensi diniyah dan sholat
- Export rekap ke PDF dan Excel
- Pengelolaan data santri
- Pengelolaan user dan role
- Hak akses untuk `Admin`, `Pembina`, dan `Ustadz Pengajar`

## Teknologi

- PHP 8.2
- Laravel 12
- MySQL
- Blade
- Bootstrap 5
- Vite

## Struktur Peran

- `Admin`: mengelola seluruh data, user, jadwal, dan presensi
- `Pembina`: melihat dan mengelola data santri sesuai akses yang diberikan
- `Ustadz Pengajar`: melihat data sesuai ruang lingkup pengajaran

## Kebutuhan Sistem

- PHP 8.2+
- Composer
- Node.js dan npm
- MySQL / MariaDB

## Instalasi

1. Clone repository:

```bash
git clone https://github.com/Famusth11/sisantri.git
cd sisantri
```

2. Install dependency:

```bash
composer install
npm install
```

3. Siapkan file environment:

```bash
copy .env.example .env
php artisan key:generate
```

4. Atur koneksi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sisantri
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migrasi:

```bash
php artisan migrate
```

Jika sudah punya file dump database `.sql`, database juga bisa diisi lewat import manual di MySQL/phpMyAdmin.

6. Build asset frontend:

```bash
npm run build
```

## Menjalankan Aplikasi

Mode sederhana:

```bash
php artisan serve
```

Untuk development frontend:

```bash
npm run dev
```

Atau gunakan script development project:

```bash
composer run dev
```

## Pengujian

Menjalankan test:

```bash
php artisan test
```

## Fitur Operasional

- Halaman dashboard
- Presensi sholat
- Presensi diniyah
- Rekap bulanan diniyah
- Rekap bulanan sholat
- Manajemen jadwal diniyah
- Manajemen santri
- Manajemen user

## Catatan Deployment

- Pastikan `APP_ENV=production`
- Pastikan `APP_DEBUG=false`
- Jangan upload file `.env` ke repository publik
- Jalankan optimasi setelah deploy:

```bash
php artisan optimize
```

- Jika ada perubahan data besar atau cache tidak sinkron:

```bash
php artisan optimize:clear
```

## Catatan Penting

- File `.env` tidak disertakan ke repository demi keamanan
- Folder `vendor` dan `node_modules` tidak disimpan di Git
- Data database produksi sebaiknya dibagikan terpisah dari source code

## Author

- GitHub: [Famusth11](https://github.com/Famusth11)
