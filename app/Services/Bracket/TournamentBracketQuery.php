<?php

namespace App\Services\Bracket;

use App\Models\Tournament;

class TournamentBracketQuery
{
    public function get(int|Tournament $tournament): Tournament
    {
        $id = $tournament instanceof Tournament ? $tournament->id : $tournament;

        return Tournament::query()
            ->with([
                'champion',
                'rounds' => fn ($query) => $query->orderBy('round_order'),
                'rounds.matches' => fn ($query) => $query->orderBy('bracket_position'),
                'rounds.matches.team1',
                'rounds.matches.team2',
                'rounds.matches.winner',
                'rounds.matches.nextMatch',
            ])
            ->findOrFail($id);
    }
}
