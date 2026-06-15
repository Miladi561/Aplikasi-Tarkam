<?php

namespace App\Livewire;

use App\Models\MatchModel;
use App\Models\Tournament;
use App\Services\Bracket\AdvanceWinnerService;
use App\Services\Bracket\BracketPositionService;
use App\Services\Bracket\TournamentBracketQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class TournamentBracket extends Component
{
    public Tournament $tournament;

    public ?int $selectedMatchId = null;

    public ?int $team1Score = null;

    public ?int $team2Score = null;

    public ?int $team1PenaltyScore = null;

    public ?int $team2PenaltyScore = null;

    public string $status = 'pending';

    public ?string $venue = null;

    public ?string $matchDate = null;

    public function mount(Tournament $tournament): void
    {
        $this->tournament = $tournament;
    }

    public function openMatch(int $matchId): void
    {
        $this->refreshTournament(app(TournamentBracketQuery::class));

        $match = $this->findMatch($matchId);

        $this->selectedMatchId = $match->id;
        $this->team1Score = $match->team1_score;
        $this->team2Score = $match->team2_score;
        $this->team1PenaltyScore = $match->team1_penalty_score;
        $this->team2PenaltyScore = $match->team2_penalty_score;
        $this->status = $match->status;
        $this->venue = $match->venue;
        $this->matchDate = $match->match_date?->format('Y-m-d\TH:i');
    }

    public function closeModal(): void
    {
        $this->reset([
            'selectedMatchId',
            'team1Score',
            'team2Score',
            'team1PenaltyScore',
            'team2PenaltyScore',
            'venue',
            'matchDate',
        ]);

        $this->status = 'pending';
    }

    public function saveScore(): void
    {
        $advanceWinnerService = app(AdvanceWinnerService::class);
        $query = app(TournamentBracketQuery::class);

        $data = $this->validate([
            'team1Score' => ['required', 'integer', 'min:0'],
            'team2Score' => ['required', 'integer', 'min:0'],
            'team1PenaltyScore' => ['nullable', 'integer', 'min:0'],
            'team2PenaltyScore' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:pending,ongoing,finished'],
            'venue' => ['nullable', 'string', 'max:255'],
            'matchDate' => ['nullable', 'date'],
        ]);

        $match = MatchModel::query()->findOrFail($this->selectedMatchId);

        $match->forceFill([
            'team1_score' => $data['team1Score'],
            'team2_score' => $data['team2Score'],
            'team1_penalty_score' => $data['team1PenaltyScore'],
            'team2_penalty_score' => $data['team2PenaltyScore'],
            'status' => $data['status'],
            'venue' => $data['venue'],
            'match_date' => $data['matchDate'],
        ])->save();

        if ($match->status === 'finished') {
            $advanceWinnerService->advance($match);
        }

        $this->refreshTournament($query);
        $this->closeModal();

        $this->dispatch('bracket-updated');
    }

    public function markByeWinner(): void
    {
        $advanceWinnerService = app(AdvanceWinnerService::class);
        $query = app(TournamentBracketQuery::class);

        $match = MatchModel::query()->findOrFail($this->selectedMatchId);

        if (! $match->is_bye) {
            throw ValidationException::withMessages([
                'selectedMatchId' => 'Match ini bukan BYE.',
            ]);
        }

        $advanceWinnerService->advance($match);
        $this->refreshTournament($query);
        $this->closeModal();
    }

    /**
     * Swap teams between matches and team slots.
     * Can swap a team into another pending/open slot.
     */
    public function swapTeams(int $sourceMatchId, string $sourceSlot, int $targetMatchId, string $targetSlot): void
    {
        $query = app(TournamentBracketQuery::class);
        $advanceWinnerService = app(AdvanceWinnerService::class);

        $sourceMatch = MatchModel::query()->findOrFail($sourceMatchId);
        $targetMatch = MatchModel::query()->findOrFail($targetMatchId);

        $sourceField = $sourceSlot === 'team1' ? 'team1_id' : 'team2_id';
        $targetField = $targetSlot === 'team1' ? 'team1_id' : 'team2_id';

        $sourceTeamId = $sourceMatch->{$sourceField};
        $targetTeamId = $targetMatch->{$targetField};

        // Swap actual team IDs
        $sourceMatch->forceFill([$sourceField => $targetTeamId])->save();
        $targetMatch->forceFill([$targetField => $sourceTeamId])->save();

        // Re-evaluate source match if finished or is a bye
        if ($sourceMatch->status === 'finished') {
            $advanceWinnerService->advance($sourceMatch);
        } else if ($sourceMatch->is_bye) {
            $winnerId = $sourceMatch->team1_id ?? $sourceMatch->team2_id;
            $sourceMatch->forceFill(['winner_team_id' => $winnerId])->save();
            if ($sourceMatch->next_match_id && $sourceMatch->next_match_slot) {
                MatchModel::query()->where('id', $sourceMatch->next_match_id)->update([
                    $sourceMatch->next_match_slot.'_id' => $winnerId
                ]);
            }
        }

        // Re-evaluate target match if finished or is a bye
        if ($targetMatch->status === 'finished') {
            $advanceWinnerService->advance($targetMatch);
        } else if ($targetMatch->is_bye) {
            $winnerId = $targetMatch->team1_id ?? $targetMatch->team2_id;
            $targetMatch->forceFill(['winner_team_id' => $winnerId])->save();
            if ($targetMatch->next_match_id && $targetMatch->next_match_slot) {
                MatchModel::query()->where('id', $targetMatch->next_match_id)->update([
                    $targetMatch->next_match_slot.'_id' => $winnerId
                ]);
            }
        }

        $this->refreshTournament($query);
        $this->dispatch('bracket-updated');
    }

    public function render(): View
    {
        $this->refreshTournament(app(TournamentBracketQuery::class));
        $layout = app(BracketPositionService::class)->build($this->tournament);

        return view('livewire.tournament-bracket', [
            'layout' => $layout,
            'selectedMatch' => $this->selectedMatchModel(),
        ]);
    }

    private function refreshTournament(TournamentBracketQuery $query): void
    {
        $this->tournament = $query->get($this->tournament);
    }

    private function findMatch(int $matchId): MatchModel
    {
        $match = $this->tournament
            ->rounds
            ->flatMap->matches
            ->firstWhere('id', $matchId);

        abort_if($match === null, 404);

        return $match;
    }

    private function selectedMatchModel(): ?MatchModel
    {
        if ($this->selectedMatchId === null) {
            return null;
        }

        return $this->tournament
            ->rounds
            ->flatMap->matches
            ->firstWhere('id', $this->selectedMatchId);
    }
}
