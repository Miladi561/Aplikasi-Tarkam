<?php

namespace App\Services\Bracket;

use App\Models\MatchModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdvanceWinnerService
{
    public function advance(MatchModel $match): MatchModel
    {
        return DB::transaction(function () use ($match): MatchModel {
            $match = MatchModel::query()
                ->with(['tournament', 'nextMatch'])
                ->lockForUpdate()
                ->findOrFail($match->id);

            $winnerTeamId = $this->determineWinnerTeamId($match);

            $match->forceFill([
                'winner_team_id' => $winnerTeamId,
                'status' => 'finished',
            ])->save();

            if ($match->next_match_id !== null && $match->next_match_slot !== null) {
                $this->fillNextMatchSlot($match, $winnerTeamId);
            } else {
                $match->tournament?->forceFill([
                    'champion_team_id' => $winnerTeamId,
                    'status' => 'finished',
                ])->save();
            }

            return $match->refresh();
        });
    }

    private function determineWinnerTeamId(MatchModel $match): int
    {
        if ($match->team1_id === null && $match->team2_id === null) {
            throw ValidationException::withMessages([
                'teams' => 'Match belum memiliki team.',
            ]);
        }

        if ($match->is_bye) {
            return (int) ($match->team1_id ?? $match->team2_id);
        }

        if ($match->team1_id === null || $match->team2_id === null) {
            throw ValidationException::withMessages([
                'teams' => 'Match normal harus memiliki dua team.',
            ]);
        }

        if ($match->team1_score > $match->team2_score) {
            return (int) $match->team1_id;
        }

        if ($match->team2_score > $match->team1_score) {
            return (int) $match->team2_id;
        }

        if ($match->team1_penalty_score === null || $match->team2_penalty_score === null) {
            throw ValidationException::withMessages([
                'penalty' => 'Skor seri membutuhkan skor penalti.',
            ]);
        }

        if ($match->team1_penalty_score === $match->team2_penalty_score) {
            throw ValidationException::withMessages([
                'penalty' => 'Skor penalti tidak boleh seri.',
            ]);
        }

        return $match->team1_penalty_score > $match->team2_penalty_score
            ? (int) $match->team1_id
            : (int) $match->team2_id;
    }

    private function fillNextMatchSlot(MatchModel $match, int $winnerTeamId): void
    {
        $nextMatch = MatchModel::query()
            ->lockForUpdate()
            ->findOrFail($match->next_match_id);

        $nextMatch->forceFill([
            $match->next_match_slot.'_id' => $winnerTeamId,
        ])->save();

        // Jika match tujuan adalah BYE yang menunggu pemenang dari match sebelumnya,
        // pemenang langsung diteruskan lagi sampai menemukan match normal berikutnya.
        if ($nextMatch->is_bye && ($nextMatch->team1_id !== null || $nextMatch->team2_id !== null)) {
            $this->advance($nextMatch);
        }
    }
}
