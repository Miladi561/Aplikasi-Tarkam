<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\Tournament;
use App\Services\Bracket\GenerateBracketService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedLocalCupBracketCommand extends Command
{
    protected $signature = 'tournament:seed-local-cup {--teams=42}';

    protected $description = 'Seed contoh tournament lokal dan generate bracket.';

    public function handle(GenerateBracketService $service): int
    {
        $teamCount = max(2, (int) $this->option('teams'));

        DB::transaction(function () use ($teamCount, $service): void {
            $tournament = Tournament::query()->create([
                'name' => 'Tirtatama Star Connect Cup',
                'year' => 2026,
                'status' => 'draft',
            ]);

            for ($i = 1; $i <= $teamCount; $i++) {
                $team = Team::query()->create([
                    'name' => 'Team '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                ]);

                $tournament->teams()->attach($team->id, [
                    'seed_number' => $i,
                    'is_bye' => false,
                ]);
            }

            $service->generate($tournament, true);
        });

        $this->info("Seed {$teamCount} team dan bracket selesai dibuat.");

        return self::SUCCESS;
    }
}
