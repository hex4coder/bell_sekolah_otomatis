<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->dropIndex(['day_of_week', 'order']);
            $table->dropColumn('order');
        });
    }

    public function down(): void
    {
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->unsignedTinyInteger('order')->default(0)->after('audio_file');
            $table->index(['day_of_week', 'order']);
        });
    }
};
