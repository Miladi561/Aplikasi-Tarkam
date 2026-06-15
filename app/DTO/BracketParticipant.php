<?php

namespace App\DTO;

final readonly class BracketParticipant
{
    public function __construct(
        public ?int $teamId,
        public ?int $sourceMatchId = null,
    ) {}

    public static function team(int $teamId): self
    {
        return new self($teamId);
    }

    public static function match(int $matchId, ?int $knownWinnerTeamId = null): self
    {
        return new self($knownWinnerTeamId, $matchId);
    }
}
