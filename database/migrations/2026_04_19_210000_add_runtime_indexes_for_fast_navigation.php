<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            if (!$this->indexExists('santri', 'santri_nama_index')) {
                $table->index('nama', 'santri_nama_index');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_name_index')) {
                $table->index('name', 'users_name_index');
            }

            if (!$this->indexExists('users', 'users_role_name_index')) {
                $table->index(['role', 'name'], 'users_role_name_index');
            }
        });

        Schema::table('absensi', function (Blueprint $table) {
            if (!$this->indexExists('absensi', 'absensi_santri_timestamp_index')) {
                $table->index(['santri_id', 'timestamp'], 'absensi_santri_timestamp_index');
            }

            if (!$this->indexExists('absensi', 'absensi_kegiatan_timestamp_index')) {
                $table->index(['kegiatan', 'timestamp'], 'absensi_kegiatan_timestamp_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropIndex('santri_nama_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_role_name_index');
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex('absensi_santri_timestamp_index');
            $table->dropIndex('absensi_kegiatan_timestamp_index');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();

            if ($driver === 'sqlite') {
                $result = $connection->select(
                    "SELECT name FROM sqlite_master WHERE type='index' AND name=? AND tbl_name=?",
                    [$indexName, $table]
                );

                return count($result) > 0;
            }

            $database = $connection->getDatabaseName();
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$database, $table, $indexName]
            );

            return isset($result[0]) && (int) $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
