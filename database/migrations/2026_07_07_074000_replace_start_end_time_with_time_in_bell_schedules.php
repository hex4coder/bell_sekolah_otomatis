<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->time('time')->nullable()->after('name');
        });

        DB::statement('UPDATE bell_schedules SET `time` = start_time');

        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });

        DB::statement('ALTER TABLE bell_schedules MODIFY `time` TIME NOT NULL');
    }

    public function down(): void
    {
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });

        DB::statement('UPDATE bell_schedules SET start_time = `time`');

        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->dropColumn('time');
        });

        DB::statement('ALTER TABLE bell_schedules MODIFY start_time TIME NOT NULL');
    }
};
