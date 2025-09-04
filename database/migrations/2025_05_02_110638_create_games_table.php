<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::dropIfExists('games');
        Schema::create('games', function (Blueprint $table) {
            $table->id();

            // Scheduling
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedSmallInteger('field');
            $table->unsignedSmallInteger('match_length_minutes')->default(15);

            // Teams
            $table->foreignId('team_1_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('team_2_id')->constrained('teams')->cascadeOnDelete();

            // Submission workflow
            $table->string('team_1_submission')->nullable();
            $table->string('team_2_submission')->nullable();
            $table->enum('status', ['pending', 'conflict', 'accepted'])->default('pending');
            $table->string('accepted_outcome')->nullable();
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->boolean('points_applied')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['start_time']);
            $table->index(['field', 'start_time']);
            $table->index(['team_1_id']);
            $table->index(['team_2_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('games');
    }
};
