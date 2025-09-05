<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'email')) {
                $table->string('email')->nullable()->after('lastName');
            }
            if (!Schema::hasColumn('players', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('email');
            }
            // Make team_id nullable if exists
            if (Schema::hasColumn('players', 'team_id')) {
                $table->foreignId('team_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            if (Schema::hasColumn('players', 'email')) {
                $table->dropColumn('email');
            }
            // Optionally revert team_id to not nullable (safer to leave nullable on down)
        });
    }
};
