<?php

namespace App\Actions;

use App\Models\Tournament;
use App\Services\Bracket\GenerateBracketService;
use Illuminate\Database\Eloquent\Collection;

class GenerateTournamentBracketAction
{
    public function __construct(
        private readonly GenerateBracketService $service,
    ) {}

    /**
     * @return Collection<int, \App\Models\Round>
     */
    public function execute(Tournament $tournament, bool $force = false): Collection
    {
        return $this->service->generate($tournament, $force);
    }
}
