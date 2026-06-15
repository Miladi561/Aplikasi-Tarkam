<?php

namespace App\Services\Bracket;

use App\DTO\BracketParticipant;
use App\Models\MatchModel;
use App\Models\Round;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateBracketService
{
    /**
     * @return Collection<int, Round>
     */
    public function generate(Tournament $tournament, bool $force = false): Collection
    {
        return DB::transaction(function () use ($tournament, $force): Collection {
            $tournament = Tournament::query()
                ->with(['teams' => fn ($query) => $query->orderByPivot('seed_number')->orderBy('teams.name')])
                ->lockForUpdate()
                ->findOrFail($tournament->id);

            $this->guardCanGenerate($tournament, $force);

            $teamIds = $tournament->teams->pluck('id')->map(fn ($id): int => (int) $id)->values();

            if ($teamIds->count() < 2) {
                throw ValidationException::withMessages([
                    'teams' => 'Tournament minimal membutuhkan 2 team.',
                ]);
            }

            $tournament->matches()->delete();
            $tournament->rounds()->delete();

            DB::table('tournament_teams')
                ->where('tournament_id', $tournament->id)
                ->update(['is_bye' => false]);

            $participants = $teamIds
                ->map(fn (int $teamId): BracketParticipant => BracketParticipant::team($teamId))
                ->all();

            $rounds = new Collection();
            $roundOrder = 1;

            while (count($participants) > 1) {
                $byeCount = $this->byeCountForRound(count($participants), $roundOrder);
                $entries = $this->buildRoundEntries($participants, $byeCount);
                $round = $this->createRound($tournament, $roundOrder, count($participants));
                $rounds->push($round);

                $nextParticipants = [];

                foreach ($entries as $position => $entry) {
                    /** @var BracketParticipant|null $team1 */
                    $team1 = $entry[0] ?? null;
                    /** @var BracketParticipant|null $team2 */
                    $team2 = $entry[1] ?? null;
                    $isBye = $team2 === null;
                    $winnerTeamId = $isBye ? $team1?->teamId : null;

                    $match = MatchModel::query()->create([
                        'tournament_id' => $tournament->id,
                        'round_id' => $round->id,
                        'bracket_position' => $position + 1,
                        'team1_id' => $team1?->teamId,
                        'team2_id' => $team2?->teamId,
                        'winner_team_id' => $winnerTeamId,
                        'is_bye' => $isBye,
                        'status' => $isBye ? 'finished' : 'pending',
                    ]);

                    // Link sumber pemenang ke match berikutnya begitu match tujuan dibuat.
                    // Inilah bagian yang membuat winner bisa otomatis masuk slot team1/team2.
                    $this->linkSourceMatch($team1, $match, 'team1');
                    $this->linkSourceMatch($team2, $match, 'team2');

                    if ($roundOrder === 1 && $isBye && $winnerTeamId !== null) {
                        DB::table('tournament_teams')
                            ->where('tournament_id', $tournament->id)
                            ->where('team_id', $winnerTeamId)
                            ->update(['is_bye' => true]);
                    }

                    $nextParticipants[] = BracketParticipant::match($match->id, $winnerTeamId);
                }

                $participants = $nextParticipants;
                $roundOrder++;
            }

            $tournament->forceFill([
                'status' => 'generated',
                'champion_team_id' => null,
                'bracket_generated_at' => now(),
            ])->save();

            return $rounds->load(['matches.team1', 'matches.team2', 'matches.nextMatch']);
        });
    }

    private function guardCanGenerate(Tournament $tournament, bool $force): void
    {
        if ($force) {
            return;
        }

        $hasPlayedMatch = $tournament->matches()
            ->where('is_bye', false)
            ->where(function ($query): void {
                $query->where('status', '!=', 'pending')
                    ->orWhereNotNull('winner_team_id');
            })
            ->exists();

        if ($hasPlayedMatch) {
            throw ValidationException::withMessages([
                'tournament' => 'Bracket sudah memiliki pertandingan berjalan/selesai. Gunakan force jika ingin generate ulang.',
            ]);
        }
    }

    private function byeCountForRound(int $participantCount, int $roundOrder): int
    {
        if ($participantCount <= 1) {
            return 0;
        }

        if ($roundOrder === 1) {
            $lowerPowerOfTwo = 2 ** (int) floor(log($participantCount, 2));

            return $participantCount - $lowerPowerOfTwo;
        }

        return $participantCount % 2;
    }

    /**
     * @param array<int, BracketParticipant> $participants
     * @return array<int, array<int, BracketParticipant>>
     */
    private function buildRoundEntries(array $participants, int $byeCount): array
    {
        $participantCount = count($participants);
        $matchCount = intdiv($participantCount - $byeCount, 2);
        $entryCount = $matchCount + $byeCount;
        $byeIndexes = $this->distributedByeIndexes($entryCount, $byeCount);
        $entries = [];
        $cursor = 0;

        // BYE disebar merata, bukan ditumpuk di akhir, agar bracket lokal terlihat seimbang.
        for ($entryIndex = 0; $entryIndex < $entryCount; $entryIndex++) {
            if (in_array($entryIndex, $byeIndexes, true)) {
                $entries[] = [$participants[$cursor++]];

                continue;
            }

            $entries[] = [
                $participants[$cursor++],
                $participants[$cursor++],
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, int>
     */
    private function distributedByeIndexes(int $entryCount, int $byeCount): array
    {
        if ($byeCount === 0) {
            return [];
        }

        $indexes = [];
        $step = $entryCount / $byeCount;

        for ($i = 0; $i < $byeCount; $i++) {
            $candidate = (int) floor(($i + 0.5) * $step);

            while (in_array($candidate, $indexes, true) && $candidate < $entryCount - 1) {
                $candidate++;
            }

            $indexes[] = min($candidate, $entryCount - 1);
        }

        sort($indexes);

        return $indexes;
    }

    private function createRound(Tournament $tournament, int $roundOrder, int $participantCount): Round
    {
        return Round::query()->create([
            'tournament_id' => $tournament->id,
            'name' => $this->roundName($participantCount),
            'round_type' => $this->roundType($participantCount),
            'round_order' => $roundOrder,
        ]);
    }

    private function roundName(int $participantCount): string
    {
        return match ($participantCount) {
            2 => 'Final',
            4 => 'Semi Final',
            8 => 'Quarter Final',
            default => 'Round '.$participantCount,
        };
    }

    private function roundType(int $participantCount): string
    {
        return match ($participantCount) {
            2 => 'final',
            4 => 'semi_final',
            8 => 'quarter_final',
            default => 'round_'.$participantCount,
        };
    }

    private function linkSourceMatch(?BracketParticipant $participant, MatchModel $nextMatch, string $slot): void
    {
        if ($participant?->sourceMatchId === null) {
            return;
        }

        MatchModel::query()
            ->whereKey($participant->sourceMatchId)
            ->update([
                'next_match_id' => $nextMatch->id,
                'next_match_slot' => $slot,
            ]);
    }
}
