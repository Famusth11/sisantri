<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    


    public function up(): void
    {
        Schema::create('santri', function (Blueprint $table) {
            $table->string('id_santri')->primary(); 
            $table->string('nama');
            $table->string('jenis_kelamin'); 
            $table->string('kelas'); 
            $table->string('golongan')->nullable(); 
            $table->string('pembina')->nullable(); 
            $table->timestamps();
            
            
            $table->index('kelas');
            $table->index('golongan');
        });
    }

    


    public function down(): void
    {
        Schema::dropIfExists('santri');
    }
};
