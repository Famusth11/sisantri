<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_diniyah', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_diniyah', 'tanggal_jadwal')) {
                $table->date('tanggal_jadwal')->nullable()->after('semester');
                $table->index('tanggal_jadwal', 'jadwal_diniyah_tanggal_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_diniyah', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_diniyah', 'tanggal_jadwal')) {
                $table->dropIndex('jadwal_diniyah_tanggal_index');
                $table->dropColumn('tanggal_jadwal');
            }
        });
    }
};
