@props([
    'match',
    'position',
    'cardWidth',
    'cardHeight',
    'isRight' => false,
    'labelNumber' => 1
])

@php
    $team1Winner = $match->winner_team_id !== null && $match->winner_team_id === $match->team1_id;
    $team2Winner = $match->winner_team_id !== null && $match->winner_team_id === $match->team2_id;

    $statusClass = match($match->status) {
        'finished' => 'bracket-status-finished',
        'ongoing' => 'bracket-status-ongoing',
        default => 'bracket-status-pending',
    };

    $statusLabel = match($match->status) {
        'finished' => 'Final',
        'ongoing' => 'Live',
        default => 'Upcoming',
    };

    $dateLabel = $match->match_date
        ? $match->match_date->format('d M')
        : 'TBD';
@endphp

<div
    class="bracket-card"
    style="left: {{ $position['left'] }}px; top: {{ $position['top'] }}px; width: {{ $cardWidth }}px; height: {{ $cardHeight }}px;"
>
    <div class="bracket-card-header">
        <span class="truncate">Match {{ str_pad((string) $labelNumber, 2, '0', STR_PAD_LEFT) }} &bull; {{ strtoupper($dateLabel) }}</span>
        <span class="match-status-pill {{ $statusClass }}">{{ strtoupper($statusLabel) }}</span>
    </div>

    <div class="teams-container">
        <div
            x-data="{ active: false }"
            draggable="true"
            @dragstart="e => { e.dataTransfer.setData('text/plain', JSON.stringify({ matchId: {{ $match->id }}, slot: 'team1' })) }"
            @dragover.prevent=""
            @dragenter="active = true"
            @dragleave="active = false"
            @drop="e => {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                $wire.swapTeams(data.matchId, data.slot, {{ $match->id }}, 'team1');
                active = false;
            }"
            class="team-slot"
            :class="active ? 'team-slot-active-drag' : ''"
        >
            <div class="team-info">
                <div class="team-logo-placeholder">
                    {{ substr($match->team1?->name ?? '?', 0, 1) }}
                </div>
                <span class="team-name {{ $team1Winner ? 'winner' : '' }}">
                    {{ $match->team1?->name ?? 'TBD' }}
                </span>
            </div>

            <div class="score-info">
                @if ($team1Winner)
                    <span class="winner-badge">Winner</span>
                @endif
                <span class="team-score {{ $team1Winner ? 'winner' : '' }}">
                    {{ $match->team1_score ?? '-' }}
                </span>
            </div>
        </div>

        <div
            x-data="{ active: false }"
            draggable="true"
            @dragstart="e => { e.dataTransfer.setData('text/plain', JSON.stringify({ matchId: {{ $match->id }}, slot: 'team2' })) }"
            @dragover.prevent=""
            @dragenter="active = true"
            @dragleave="active = false"
            @drop="e => {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                $wire.swapTeams(data.matchId, data.slot, {{ $match->id }}, 'team2');
                active = false;
            }"
            class="team-slot"
            :class="active ? 'team-slot-active-drag' : ''"
        >
            <div class="team-info">
                <div class="team-logo-placeholder">
                    {{ substr($match->team2?->name ?? ($match->is_bye ? 'B' : '?'), 0, 1) }}
                </div>
                <span class="team-name {{ $team2Winner ? 'winner' : '' }}">
                    {{ $match->team2?->name ?? ($match->is_bye ? 'AUTO WIN / BYE' : 'TBD') }}
                </span>
            </div>

            <div class="score-info">
                @if ($team2Winner)
                    <span class="winner-badge">Winner</span>
                @endif
                <span class="team-score {{ $team2Winner ? 'winner' : '' }}">
                    {{ $match->team2_score ?? '-' }}
                </span>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="openMatch({{ $match->id }})"
        class="absolute inset-0 z-10 cursor-pointer bg-transparent"
        style="border: 0; outline: none;"
        title="Edit Match Details"
    ></button>
</div>
