<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bell_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->comment('0=Sunday, 1=Monday, ..., 6=Saturday');
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('audio_file')->nullable();
            $table->unsignedTinyInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['day_of_week', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bell_schedules');
    }
};
