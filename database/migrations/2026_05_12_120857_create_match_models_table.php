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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            /*
                        |--------------------------------------------------------------------------
                        | Tournament & Round
                        |--------------------------------------------------------------------------
                        */

            $table->foreignId('tournament_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('round_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Bracket Position
            |--------------------------------------------------------------------------
            */

            $table->integer('bracket_position');

            /*
            |--------------------------------------------------------------------------
            | Teams
            |--------------------------------------------------------------------------
            */

            $table->foreignId('team1_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            $table->foreignId('team2_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Scores
            |--------------------------------------------------------------------------
            */

            $table->integer('team1_score')->default(0);
            $table->integer('team2_score')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Penalty Score
            |--------------------------------------------------------------------------
            */

            $table->integer('team1_penalty_score')->nullable();
            $table->integer('team2_penalty_score')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Winner
            |--------------------------------------------------------------------------
            */

            $table->foreignId('winner_team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Match Information
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_bye')->default(false);

            $table->enum('status', [
                'pending',
                'ongoing',
                'finished',
            ])->default('pending');

            $table->dateTime('match_date')->nullable();

            $table->string('venue')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Next Match Relation
            |--------------------------------------------------------------------------
            */

            $table->foreignId('next_match_id')
                ->nullable()
                ->constrained('matches')
                ->nullOnDelete();

            $table->enum('next_match_slot', [
                'team1',
                'team2',
            ])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
