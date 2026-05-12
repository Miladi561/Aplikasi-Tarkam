<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Event Type
            |--------------------------------------------------------------------------
            */

            $table->enum('event_type', [
                'goal',
                'yellow_card',
                'red_card',
                'own_goal',
                'penalty_goal',
                'miss_penalty',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Player Information
            |--------------------------------------------------------------------------
            */

            $table->string('player_name')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Match Minute
            |--------------------------------------------------------------------------
            */

            $table->integer('minute')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};
