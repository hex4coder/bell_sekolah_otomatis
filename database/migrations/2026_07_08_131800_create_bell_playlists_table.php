<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bell_playlists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['opening', 'closing']);
            $table->string('name');
            $table->json('audio_assets');
            $table->time('time_range_start')->nullable();
            $table->time('time_range_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('day_of_week')->nullable();
            $table->string('action_after')->nullable();
            $table->integer('action_after_delay')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bell_playlists');
    }
};
