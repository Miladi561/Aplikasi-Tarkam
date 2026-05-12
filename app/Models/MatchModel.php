<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchModel extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'round_id',

        'bracket_position',

        'team1_id',
        'team2_id',

        'team1_score',
        'team2_score',

        'team1_penalty_score',
        'team2_penalty_score',

        'winner_team_id',

        'is_bye',

        'status',

        'match_date',
        'venue',

        'next_match_id',
        'next_match_slot',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'is_bye' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function team1(): BelongsTo
    {
        return $this->belongsTo(
            Team::class,
            'team1_id'
        );
    }

    public function team2(): BelongsTo
    {
        return $this->belongsTo(
            Team::class,
            'team2_id'
        );
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(
            Team::class,
            'winner_team_id'
        );
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(
            MatchModel::class,
            'next_match_id'
        );
    }

    public function previousMatches(): HasMany
    {
        return $this->hasMany(
            MatchModel::class,
            'next_match_id'
        );
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */

    public function getScoreAttribute(): string
    {
        return $this->team1_score . ' - ' . $this->team2_score;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function hasPenalty(): bool
    {
        return ! is_null($this->team1_penalty_score);
    }
}
