<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('outcome')->nullable();
            $table->dateTime('startTime');
            $table->dateTime('endTime');
            $table->foreignId('team_1_id');
            $table->foreignId('team_2_id');
            $table->integer('field');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('matches');
    }
};
