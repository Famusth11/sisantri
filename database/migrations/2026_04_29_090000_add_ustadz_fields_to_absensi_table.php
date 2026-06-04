<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi', 'nama_ustadz')) {
                $table->string('nama_ustadz')->nullable()->after('petugas_id');
            }

            if (!Schema::hasColumn('absensi', 'kelas_mengajar')) {
                $table->string('kelas_mengajar')->nullable()->after('nama_ustadz');
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            if (Schema::hasColumn('absensi', 'kelas_mengajar')) {
                $table->dropColumn('kelas_mengajar');
            }

            if (Schema::hasColumn('absensi', 'nama_ustadz')) {
                $table->dropColumn('nama_ustadz');
            }
        });
    }
};
