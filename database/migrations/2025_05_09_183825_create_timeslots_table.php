<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timeslots', function (Blueprint $table) {
            $table->id();
            $table->integer('fields');
            $table->json('games');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeslots');
    }
};
