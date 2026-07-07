<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom 'time' baru (sementara nullable)
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->time('time')->nullable()->after('name');
        });

        // 2. Salin data dari start_time ke time
        DB::statement('UPDATE bell_schedules SET `time` = start_time');

        // 3. Hapus kolom lama
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });

        // 4. Ubah kolom 'time' menjadi NOT NULL menggunakan fitur bawaan Laravel
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->time('time')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        // 1. Kembalikan kolom start_time dan end_time (sementara nullable)
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });

        // 2. Kembalikan data dari time ke start_time
        DB::statement('UPDATE bell_schedules SET start_time = `time`');

        // 3. Hapus kolom time
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->dropColumn('time');
        });

        // 4. Ubah start_time menjadi NOT NULL menggunakan fitur bawaan Laravel
        Schema::table('bell_schedules', function (Blueprint $table) {
            $table->time('start_time')->nullable(false)->change();
        });
    }
};