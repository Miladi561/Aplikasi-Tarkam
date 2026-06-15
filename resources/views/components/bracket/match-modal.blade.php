@props([
    'selectedMatch',
    'errors'
])

<div
    class="bracket-modal-backdrop"
    x-data
    x-on:keydown.escape.window="$wire.closeModal()"
    wire:transition.opacity
>
    <div class="bracket-modal-container">
        <!-- Header -->
        <div class="bracket-modal-header">
            <div>
                <p class="bracket-modal-label" style="color: #0284c7;">{{ $selectedMatch->round?->name }}</p>
                <h3 class="bracket-modal-title">Match #{{ $selectedMatch->bracket_position }}</h3>
            </div>

            <button type="button" wire:click="closeModal" class="bracket-modal-close-btn">
                Close
            </button>
        </div>

        <form wire:submit.prevent="saveScore" class="bracket-modal-body">
            <div class="bracket-modal-form-grid md:grid-cols-2">
                <div class="bracket-modal-team-card">
                    <p class="bracket-modal-label">Team 1</p>
                    <p class="bracket-modal-team-name">{{ $selectedMatch->team1?->name ?? 'TBD (Winner previous match)' }}</p>
                </div>

                <div class="bracket-modal-team-card">
                    <p class="bracket-modal-label">Team 2</p>
                    <p class="bracket-modal-team-name {{ $selectedMatch->is_bye ? 'italic text-sky-600' : '' }}">
                        {{ $selectedMatch->team2?->name ?? ($selectedMatch->is_bye ? 'AUTO WIN / BYE' : 'TBD (Winner previous match)') }}
                    </p>
                </div>
            </div>

            <div class="bracket-score-section">
                <p class="bracket-score-title">Skor Normal</p>
                <div class="bracket-modal-input-group">
                    <label class="bracket-modal-field">
                        <span>{{ $selectedMatch->team1?->name ?? 'Team 1' }}</span>
                        <input type="number" min="0" wire:model="team1Score" class="bracket-modal-input">
                    </label>
                    <label class="bracket-modal-field">
                        <span>{{ $selectedMatch->team2?->name ?? 'Team 2' }}</span>
                        <input type="number" min="0" wire:model="team2Score" class="bracket-modal-input">
                    </label>
                </div>
            </div>

            <div class="bracket-score-section">
                <p class="bracket-score-title">Skor Penalti</p>
                <div class="bracket-modal-input-group">
                    <label class="bracket-modal-field">
                        <span>{{ $selectedMatch->team1?->name ?? 'Team 1' }}</span>
                        <input type="number" min="0" wire:model="team1PenaltyScore" placeholder="Kosongkan jika tidak ada" class="bracket-modal-input">
                    </label>
                    <label class="bracket-modal-field">
                        <span>{{ $selectedMatch->team2?->name ?? 'Team 2' }}</span>
                        <input type="number" min="0" wire:model="team2PenaltyScore" placeholder="Kosongkan jika tidak ada" class="bracket-modal-input">
                    </label>
                </div>
            </div>

            <div class="bracket-modal-meta-grid">
                <label class="bracket-modal-field">
                    <span>Status</span>
                    <select wire:model="status" class="bracket-modal-select">
                        <option value="pending">Pending</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="finished">Finished</option>
                    </select>
                </label>
                <label class="bracket-modal-field">
                    <span>Match Date</span>
                    <input type="datetime-local" wire:model="matchDate" class="bracket-modal-input">
                </label>
                <label class="bracket-modal-field">
                    <span>Venue</span>
                    <input type="text" wire:model="venue" class="bracket-modal-input">
                </label>
            </div>

            @if ($errors->any())
                <div class="bracket-modal-error" style="margin-bottom: 20px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bracket-modal-footer">
                @if ($selectedMatch->is_bye)
                    <button type="button" wire:click="markByeWinner" class="bracket-btn-advance-bye">
                        Advance BYE
                    </button>
                @endif
                <button type="button" wire:click="closeModal" class="bracket-btn-cancel">
                    Cancel
                </button>
                <button type="submit" class="bracket-btn-submit">
                    Save Match
                </button>
            </div>
        </form>
    </div>
</div>
