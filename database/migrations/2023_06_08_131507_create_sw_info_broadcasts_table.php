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
        Schema::create('sw_info_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained();
            $table->foreignId('station_programme_id')->nullable()->constrained();
            $table->foreignId('language_id')->constrained();
            $table->foreignId('transmitter_id')->constrained();
            $table->unsignedSmallInteger('frequency');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('weekdays');
            $table->unsignedSmallInteger('strength');
            $table->string('azimuth', 5);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sw_info_broadcasts');
    }
};
