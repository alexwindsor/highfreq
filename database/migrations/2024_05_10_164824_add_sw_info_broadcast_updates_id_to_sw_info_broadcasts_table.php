<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sw_info_broadcasts', function (Blueprint $table) {
            $table->foreignId('sw_info_broadcast_updates_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sw_info_broadcasts', function (Blueprint $table) {
            $table->dropColumn('sw_info_broadcast_updates_id');
        });
    }
};
