<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Contracts\View\View;

class TournamentBracketController extends Controller
{
    public function show(Tournament $tournament): View
    {
        $tournament->load([
            'champion',
            'rounds' => fn ($query) => $query->orderBy('round_order'),
            'rounds.matches' => fn ($query) => $query->orderBy('bracket_position'),
            'rounds.matches.team1',
            'rounds.matches.team2',
            'rounds.matches.winner',
        ]);

        return view('tournaments.bracket', [
            'tournament' => $tournament,
        ]);
    }
}
