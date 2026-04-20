<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
    


    public function up(): void
    {
        if (Config::get('database.default') === 'sqlite') {
            return;
        }

        
        if (Schema::hasTable('santri')) {
            
            if (Schema::hasColumn('santri', 'id')) {
                
                try {
                    DB::statement('ALTER TABLE `santri` DROP FOREIGN KEY IF EXISTS `santri_id_foreign`');
                } catch (\Exception $e) {
                    
                }
                
                
                Schema::table('santri', function (Blueprint $table) {
                    $table->dropColumn('id');
                });
            }
            
            
            if (!Schema::hasColumn('santri', 'id_santri')) {
                Schema::table('santri', function (Blueprint $table) {
                    $table->string('id_santri')->primary()->first();
                });
            } else {
                
                try {
                    DB::statement('ALTER TABLE `santri` DROP PRIMARY KEY');
                } catch (\Exception $e) {
                    
                }
                
                DB::statement('ALTER TABLE `santri` ADD PRIMARY KEY (`id_santri`)');
            }
        }
    }

    


    public function down(): void
    {
        
    }
};
