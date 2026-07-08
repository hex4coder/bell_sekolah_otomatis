<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bell_playlists', function (Blueprint $table) {
            $table->dropColumn(['action_after', 'action_after_delay', 'custom_command']);
        });
    }

    public function down(): void
    {
        Schema::table('bell_playlists', function (Blueprint $table) {
            $table->string('action_after')->nullable()->after('order');
            $table->integer('action_after_delay')->default(0)->after('action_after');
            $table->text('custom_command')->nullable()->after('action_after_delay');
        });
    }
};
