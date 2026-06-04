<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_diniyah_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_diniyah_id')->nullable()->constrained('jadwal_diniyah')->nullOnDelete();
            $table->string('action', 50);
            $table->string('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jadwal_diniyah_id', 'created_at'], 'jadwal_diniyah_histories_schedule_index');
            $table->index(['action', 'created_at'], 'jadwal_diniyah_histories_action_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_diniyah_histories');
    }
};
