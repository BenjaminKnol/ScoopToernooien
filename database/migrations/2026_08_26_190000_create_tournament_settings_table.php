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
        Schema::create('tournament_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number_of_fields')->default(1);
            $table->decimal('available_hours', 5, 2)->default(4);
            $table->date('tournament_day')->nullable();
            $table->string('start_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_settings');
    }
};
