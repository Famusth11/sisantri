# Checklist Siap Customer

Gunakan checklist ini sebelum aplikasi diserahkan ke customer atau dipindah ke server produksi.

## 1. Fitur Inti

- [ ] Login, logout, dan reset password berjalan tanpa error.
- [ ] Presensi sholat dapat disimpan dan muncul di rekap.
- [ ] Presensi diniyah hanya menampilkan dan menyimpan santri sesuai jadwal aktif.
- [ ] Rekap bulanan diniyah dan sholat bisa difilter serta ditampilkan dengan benar.
- [ ] Export PDF dan Excel berhasil diunduh dan isi datanya sesuai.
- [ ] Import santri dan import user sudah diuji dengan file contoh yang valid dan tidak valid.

## 2. Data dan Migrasi

- [ ] `php artisan migrate:status` menunjukkan semua migrasi penting sudah `Ran`.
- [ ] Data master minimal tersedia: user, santri, kitab diniyah, jadwal diniyah aktif.
- [ ] Tidak ada cache lama yang menyebabkan data tampil tidak sinkron.
- [ ] Setelah update data besar, `php artisan optimize:clear` dan uji ulang halaman utama.

## 3. Konfigurasi Produksi

- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_URL` sudah memakai domain atau URL server customer.
- [ ] Driver mail tidak lagi `log` jika customer membutuhkan email nyata.
- [ ] Kredensial database produksi, queue, dan cache sudah sesuai server tujuan.
- [ ] File penting seperti `.env` dan kredensial Google Sheets tidak ikut terbuka ke publik.

## 4. Keamanan dan Hak Akses

- [ ] Role `Admin`, `Pembina`, dan `Ustadz Pengajar` sudah diuji sesuai batas aksesnya.
- [ ] User non-admin tidak bisa membuka halaman manajemen user, santri, dan jadwal.
- [ ] Presensi tidak bisa disimpan untuk santri di luar akses user yang login.
- [ ] Password akun demo/default sudah diganti sebelum handoff.

## 5. Operasional

- [ ] `php artisan test` lulus.
- [ ] `npm run build` lulus.
- [ ] Log terbaru di `storage/logs/laravel.log` tidak berisi error aktif yang belum ditindaklanjuti.
- [ ] Backup database awal customer sudah disiapkan sebelum go-live.
- [ ] Ada akun admin utama yang sudah diverifikasi bisa masuk.

## 6. UAT dan Handover

- [ ] Customer atau pembimbing sudah mencoba skenario login, presensi, rekap, dan export.
- [ ] Ada catatan batasan sistem yang disampaikan secara jelas.
- [ ] Ada data demo atau panduan penggunaan singkat untuk penguji/customer.
- [ ] Ada rencana rollback atau langkah cepat jika setelah go-live ditemukan bug kritis.
