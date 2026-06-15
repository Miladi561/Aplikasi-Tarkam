<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'logo',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(
            Tournament::class,
            'tournament_teams'
        )
        ->withPivot([
            'seed_number',
            'is_bye'
        ])
        ->withTimestamps();
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(
            MatchModel::class,
            'team1_id'
        );
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(
            MatchModel::class,
            'team2_id'
        );
    }

    public function wins(): HasMany
    {
        return $this->hasMany(
            MatchModel::class,
            'winner_team_id'
        );
    }

    public function tournamentTeams(): HasMany
    {
        return $this->hasMany(TournamentTeam::class);
    }

    public function matchEvents(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    public function championships(): HasMany
    {
        return $this->hasMany(Tournament::class, 'champion_team_id');
    }
}
