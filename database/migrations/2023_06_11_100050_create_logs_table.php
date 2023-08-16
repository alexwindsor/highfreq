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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('station_id')->constrained();
            $table->foreignId('station_programme_id')->nullable()->constrained();
            $table->foreignId('language_id')->constrained();
            $table->unsignedSmallInteger('frequency');
            $table->dateTime('datetime');
            $table->unsignedSmallInteger('quality');
            $table->mediumText('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
