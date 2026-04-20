<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    


    public function up(): void
    {
        Schema::create('kitab_diniyah', function (Blueprint $table) {
            $table->id();
            $table->string('id_kitab')->unique(); 
            $table->string('kelas_kitab'); 
            $table->string('pengampu_golongan')->nullable(); 
            $table->string('nama_kitab')->nullable(); 
            $table->timestamps();
            
            
            $table->index('id_kitab');
            $table->index('kelas_kitab');
        });
    }

    


    public function down(): void
    {
        Schema::dropIfExists('kitab_diniyah');
    }
};
