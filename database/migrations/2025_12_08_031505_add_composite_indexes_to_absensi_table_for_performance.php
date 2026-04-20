<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    


    public function up(): void
    {
        if (Config::get('database.default') === 'sqlite') {
            Schema::table('absensi', function (Blueprint $table) {
                $table->index(['timestamp', 'kegiatan'], 'absensi_timestamp_kegiatan_index');
                $table->index(['timestamp', 'status'], 'absensi_timestamp_status_index');
                $table->index(['kegiatan', 'status'], 'absensi_kegiatan_status_index');
            });

            return;
        }

        Schema::table('absensi', function (Blueprint $table) {
            
            if (!$this->indexExists('absensi', 'absensi_timestamp_kegiatan_index')) {
                $table->index(['timestamp', 'kegiatan'], 'absensi_timestamp_kegiatan_index');
            }
            
            
            if (!$this->indexExists('absensi', 'absensi_timestamp_status_index')) {
                $table->index(['timestamp', 'status'], 'absensi_timestamp_status_index');
            }
            
            
            if (!$this->indexExists('absensi', 'absensi_kegiatan_status_index')) {
                $table->index(['kegiatan', 'status'], 'absensi_kegiatan_status_index');
            }
        });
    }

    


    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex('absensi_timestamp_kegiatan_index');
            $table->dropIndex('absensi_timestamp_status_index');
            $table->dropIndex('absensi_kegiatan_status_index');
        });
    }
    
    


    private function indexExists($table, $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        $result = $connection->select(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$databaseName, $table, $indexName]
        );
        
        return $result[0]->count > 0;
    }
};
