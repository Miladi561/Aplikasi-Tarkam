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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->enum('round_type', [
                'play_in',
                'round_26',
                'round_16',
                'quarter_final',
                'semi_final',
                'final',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Urutan Round
            |--------------------------------------------------------------------------
            */

            $table->integer('round_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
