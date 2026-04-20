<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    


    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp'); 
            $table->string('santri_id'); 
            $table->string('kegiatan'); 
            $table->string('status'); 
            $table->string('petugas_id')->nullable(); 
            $table->string('nama_santri')->nullable(); 
            $table->string('kelas')->nullable(); 
            $table->string('golongan')->nullable(); 
            $table->timestamps();
            
            
            $table->index('santri_id');
            $table->index('timestamp');
            $table->index('kegiatan');
            $table->index('status');
            $table->index(['timestamp', 'santri_id']);
        });
    }

    


    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
