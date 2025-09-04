<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'gender')) {
                $table->enum('gender', ['H','D'])->nullable()->after('email');
            }
            if (!Schema::hasColumn('players', 'team_code')) {
                $table->string('team_code', 10)->nullable()->after('gender');
                $table->index('team_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'team_code')) {
                $table->dropIndex(['team_code']);
                $table->dropColumn('team_code');
            }
            if (Schema::hasColumn('players', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
