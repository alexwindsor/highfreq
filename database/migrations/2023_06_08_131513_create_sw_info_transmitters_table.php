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
        Schema::create('sw_info_transmitters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique();
            $table->string('longitude', 32);
            $table->string('latitude', 32);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sw_info_transmitters');
    }
};
