<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table): void {
            $table->foreignId('champion_team_id')
                ->nullable()
                ->after('status')
                ->constrained('teams')
                ->nullOnDelete();

            $table->timestamp('bracket_generated_at')
                ->nullable()
                ->after('champion_team_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rounds MODIFY round_type VARCHAR(50) NOT NULL');
            DB::statement('ALTER TABLE tournaments MODIFY status VARCHAR(30) NOT NULL DEFAULT "draft"');
        }
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('champion_team_id');
            $table->dropColumn('bracket_generated_at');
        });
    }
};
