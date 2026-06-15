<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Services\Bracket\GenerateBracketService;
use Illuminate\Console\Command;

class GenerateBracketCommand extends Command
{
    protected $signature = 'tournament:generate-bracket {tournament_id} {--force}';

    protected $description = 'Generate knockout bracket untuk tournament.';

    public function handle(GenerateBracketService $service): int
    {
        $tournament = Tournament::query()->findOrFail((int) $this->argument('tournament_id'));
        $rounds = $service->generate($tournament, (bool) $this->option('force'));

        $this->info("Bracket generated: {$rounds->count()} rounds.");

        return self::SUCCESS;
    }
}
