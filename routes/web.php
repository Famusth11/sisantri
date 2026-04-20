<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\JadwalDiniyahController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SantriController;
use App\Http\Controllers\UserRoleController;

Route::view('/', 'welcome');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    
    Route::get('/santri/view', [SantriController::class, 'index'])->name('santri.view')->middleware('auth');
    Route::get('/santri/{id}/view', [SantriController::class, 'show'])->name('santri.view.show')->middleware('auth');
    Route::get('/santri/{id}/qr', [SantriController::class, 'showQr'])->name('santri.qr')->middleware('role:Admin,Pembina');
    
    Route::resource('santri', SantriController::class)->except(['index', 'show'])->middleware('role:Admin');
    Route::get('/santri', [SantriController::class, 'index'])->name('santri.index')->middleware('role:Admin');
    Route::post('/santri/import', [SantriController::class, 'import'])->name('santri.import')->middleware('role:Admin');
    Route::get('/santri/import/template', [SantriController::class, 'downloadImportTemplate'])->name('santri.import.template')->middleware('role:Admin');
    Route::get('/santri/{id}', [SantriController::class, 'show'])->name('santri.show')->middleware('role:Admin');
    
    
    Route::get('/user_roles/view', [UserRoleController::class, 'index'])->name('user_roles.view')->middleware('auth');
    Route::get('/user_roles/{user}/view', [UserRoleController::class, 'show'])->name('user_roles.view.show')->middleware('auth');
    
    
    Route::put('/user_roles/{user}/update-password', [UserRoleController::class, 'updatePassword'])->name('user_roles.update-password')->middleware('role:Admin');
    Route::resource('user_roles', UserRoleController::class)->except(['index', 'show'])->parameters(['user_roles' => 'user'])->middleware('role:Admin');
    Route::get('/user_roles', [UserRoleController::class, 'index'])->name('user_roles.index')->middleware('role:Admin');
    Route::post('/user_roles/import', [UserRoleController::class, 'import'])->name('user_roles.import')->middleware('role:Admin');
    Route::get('/user_roles/import/template', [UserRoleController::class, 'downloadImportTemplate'])->name('user_roles.import.template')->middleware('role:Admin');
    Route::get('/user_roles/{user}', [UserRoleController::class, 'show'])->name('user_roles.show')->middleware('role:Admin');
    Route::get('/jadwal-diniyah', [JadwalDiniyahController::class, 'index'])->name('jadwal_diniyah.index')->middleware('role:Admin');
    Route::post('/jadwal-diniyah', [JadwalDiniyahController::class, 'store'])->name('jadwal_diniyah.store')->middleware('role:Admin');
    Route::get('/jadwal-diniyah/{jadwalDiniyah}/edit', [JadwalDiniyahController::class, 'edit'])->name('jadwal_diniyah.edit')->middleware('role:Admin');
    Route::put('/jadwal-diniyah/{jadwalDiniyah}', [JadwalDiniyahController::class, 'update'])->name('jadwal_diniyah.update')->middleware('role:Admin');
    Route::delete('/jadwal-diniyah/{jadwalDiniyah}', [JadwalDiniyahController::class, 'destroy'])->name('jadwal_diniyah.destroy')->middleware('role:Admin');
    Route::post('/jadwal-diniyah/duplicate', [JadwalDiniyahController::class, 'duplicate'])->name('jadwal_diniyah.duplicate')->middleware('role:Admin');
    Route::post('/jadwal-diniyah/activate', [JadwalDiniyahController::class, 'activate'])->name('jadwal_diniyah.activate')->middleware('role:Admin');
    Route::post('/jadwal-diniyah/copy-assignments', [JadwalDiniyahController::class, 'copyAssignments'])->name('jadwal_diniyah.copy-assignments')->middleware('role:Admin');
    Route::get('absensi/sholat', [AbsensiController::class, 'sholat'])->name('absensi.sholat'); 
    Route::post('absensi/sholat', [AbsensiController::class, 'storeSholat'])->name('absensi.storeSholat');
    Route::get('absensi/diniyah', [AbsensiController::class, 'diniyah'])->name('absensi.diniyah');
    Route::post('absensi/diniyah', [AbsensiController::class, 'storeDiniyah'])->name('absensi.storeDiniyah');
    Route::get('absensi/refresh', [AbsensiController::class, 'refreshData'])->name('absensi.refresh');
    Route::get('absensi/rekap-bulanan', [AbsensiController::class, 'monthlyRecap'])->name('absensi.rekapBulanan');
    Route::get('absensi/rekap-bulanan/refresh', [AbsensiController::class, 'refreshMonthlyRecap'])->name('absensi.rekapBulanan.refresh');
    Route::get('absensi/rekap-bulanan/export-pdf', [AbsensiController::class, 'exportMonthlyRecapPDF'])->name('absensi.rekapBulanan.exportPDF');
    Route::get('absensi/rekap-bulanan/export-excel', [AbsensiController::class, 'exportMonthlyRecapExcel'])->name('absensi.rekapBulanan.exportExcel');
    
    Route::get('absensi/rekap-bulanan-sholat', [AbsensiController::class, 'monthlyRecapSholat'])->name('absensi.rekapBulananSholat');
    Route::get('absensi/rekap-bulanan-sholat/refresh', [AbsensiController::class, 'refreshMonthlyRecapSholat'])->name('absensi.rekapBulananSholat.refresh');
    Route::get('absensi/rekap-bulanan-sholat/export-pdf', [AbsensiController::class, 'exportMonthlyRecapSholatPDF'])->name('absensi.rekapBulananSholat.exportPDF');
    Route::get('absensi/rekap-bulanan-sholat/export-excel', [AbsensiController::class, 'exportMonthlyRecapSholatExcel'])->name('absensi.rekapBulananSholat.exportExcel');

    
    Route::post('absensi/edit-attendance', [AbsensiController::class, 'editAttendance'])->middleware('role:Admin')->name('absensi.editAttendance');
});

require __DIR__.'/auth.php';
