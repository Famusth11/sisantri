<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    


    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            
            if (!$this->indexExists('santri', 'santri_jenis_kelamin_index')) {
                $table->index('jenis_kelamin', 'santri_jenis_kelamin_index');
            }
            
            
            if (!$this->indexExists('santri', 'santri_pembina_index')) {
                $table->index('pembina', 'santri_pembina_index');
            }
            
            
            if (!$this->indexExists('santri', 'santri_kelas_golongan_index')) {
                $table->index(['kelas', 'golongan'], 'santri_kelas_golongan_index');
            }
            
            
            if (!$this->indexExists('santri', 'santri_jenis_kelamin_kelas_index')) {
                $table->index(['jenis_kelamin', 'kelas'], 'santri_jenis_kelamin_kelas_index');
            }
        });
    }

    


    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropIndex('santri_jenis_kelamin_index');
            $table->dropIndex('santri_pembina_index');
            $table->dropIndex('santri_kelas_golongan_index');
            $table->dropIndex('santri_jenis_kelamin_kelas_index');
        });
    }

    


    private function indexExists($table, $indexName)
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
            } else {
                
                $database = $connection->getDatabaseName();
                $result = $connection->select(
                    "SELECT COUNT(*) as count FROM information_schema.statistics 
                     WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                    [$database, $table, $indexName]
                );
                return isset($result[0]) && $result[0]->count > 0;
            }
        } catch (\Exception $e) {
            
            return false;
        }
    }
};
