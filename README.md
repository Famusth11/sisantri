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