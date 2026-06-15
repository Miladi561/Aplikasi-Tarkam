@php
    use App\Services\Bracket\BracketPositionService;

    $cardWidth = BracketPositionService::CARD_WIDTH;
    $cardHeight = BracketPositionService::CARD_HEIGHT;
    $firstRound = $tournament->rounds->first();
    $titleName = trim($tournament->name);
    $connectPosition = stripos($titleName, 'CONNECT');

    if ($connectPosition !== false) {
        $titleLead = trim(substr($titleName, 0, $connectPosition));
        $titleAccent = trim(substr($titleName, $connectPosition));
    } elseif (! str_contains($titleName, ' ')) {
        $titleLead = $titleName;
        $titleAccent = '';
    } else {
        $titleLead = (string) str($titleName)->beforeLast(' ');
        $titleAccent = (string) str($titleName)->afterLast(' ');
    }
@endphp

<div class="tarkam-app-shell" x-data="{ zoom: 1 }">
    <style>
        .tarkam-app-shell {
            min-height: 100vh;
            background: #071423;
            color: #edf6ff;
            font-family: 'Inter', 'Outfit', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: hidden;
        }

        .tarkam-topbar {
            height: 110px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 30px;
            border-bottom: 1px solid rgba(108, 151, 191, .12);
            background: #071423;
        }

        .tarkam-logo {
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            border: 1px solid rgba(145, 190, 231, .38);
            background: linear-gradient(145deg, #243246, #304156);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.14), 0 14px 34px rgba(0,0,0,.24);
            color: #79cef7;
            flex: 0 0 auto;
        }

        .tarkam-title {
            min-width: 0;
            flex: 1;
            font-size: clamp(20px, 5vw, 30px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -.01em;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tarkam-title span {
            color: #79cef7;
        }

        .tarkam-icon-button {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border: 0;
            background: transparent;
            color: #dbe7f7;
            cursor: pointer;
        }

        .truncate {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .tarkam-round-tabs {
            display: flex;
            align-items: center;
            gap: 24px;
            height: 66px;
            padding: 0 31px 10px;
            overflow-x: auto;
            border-bottom: 1px solid rgba(154, 191, 224, .33);
            scrollbar-width: none;
            background: #071423;
        }

        .tarkam-round-tabs::-webkit-scrollbar {
            display: none;
        }

        .tarkam-round-tab {
            flex: 0 0 auto;
            min-width: max-content;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #d6deee;
            padding: 12px 22px;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .35em;
            text-transform: uppercase;
        }

        .tarkam-round-tab.is-active {
            color: #8ee0ff;
            border: 1px solid rgba(86, 183, 231, .46);
            background: rgba(35, 128, 178, .22);
            box-shadow: inset 0 0 18px rgba(98, 202, 255, .08);
        }

        .tarkam-bracket-area {
            position: relative;
            height: calc(100vh - 176px);
            overflow: auto;
            padding-bottom: 120px;
            background:
                linear-gradient(rgba(7, 20, 35, .66), rgba(7, 20, 35, .78)),
                url('{{ asset('images/background.png') }}') center / cover no-repeat;
            scrollbar-color: rgba(126, 208, 255, .7) rgba(9, 23, 38, .8);
            scrollbar-width: thin;
        }

        .tarkam-canvas {
            position: relative;
            min-width: max-content;
            background:
                linear-gradient(90deg, rgba(126, 208, 255, .03) 0 1px, transparent 1px 100%),
                transparent;
            background-size: 86px 100%;
        }

        .tarkam-stage-label {
            position: absolute;
            left: 31px;
            top: 68px;
            z-index: 25;
            color: #cbd8ec;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .32em;
            text-transform: uppercase;
        }

        .tarkam-stage-tick {
            position: absolute;
            top: 28px;
            left: 80%;
            width: 3px;
            height: 28px;
            border-radius: 999px;
            background: rgba(154, 202, 239, .72);
            box-shadow: 0 0 18px rgba(102, 204, 255, .28);
        }

        .bracket-svg {
            position: absolute;
            inset: 0;
            z-index: 5;
            pointer-events: none;
            overflow: visible;
        }

        .bracket-svg path {
            stroke: rgba(155, 214, 255, .92);
            stroke-width: 3px;
            stroke-linecap: square;
            stroke-linejoin: round;
            filter: drop-shadow(0 0 4px rgba(112, 204, 255, .35));
        }

        .bracket-card {
            position: absolute;
            z-index: 20;
            display: grid;
            grid-template-rows: 48px 1fr;
            overflow: hidden;
            border: 1px solid rgba(116, 143, 176, .22);
            border-radius: 14px;
            background: #142133;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.04), 0 18px 42px rgba(0,0,0,.2);
        }

        .bracket-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 22px;
            background: rgba(39, 52, 74, .72);
            color: #c8d5e8;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .match-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 11px;
            line-height: 1;
            letter-spacing: .03em;
        }

        .match-status-pill::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }

        .bracket-status-ongoing {
            color: #ffd0d4;
            background: rgba(138, 35, 54, .72);
            border: 1px solid rgba(246, 72, 97, .38);
        }

        .bracket-status-finished {
            color: #a8f7d2;
            background: rgba(31, 112, 78, .58);
            border: 1px solid rgba(94, 223, 158, .3);
        }

        .bracket-status-pending {
            color: #d8e4f5;
            background: transparent;
            border: 0;
            padding-right: 0;
        }

        .teams-container {
            display: grid;
            align-content: center;
            padding: 14px 28px 18px 22px;
        }

        .team-slot {
            min-height: 62px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid rgba(128, 154, 185, .13);
            color: #dbe7fb;
            cursor: grab;
            user-select: none;
        }

        .team-slot:last-child {
            border-bottom: 0;
        }

        .team-slot:active {
            cursor: grabbing;
        }

        .team-slot-active-drag {
            color: #8ee0ff;
            background: rgba(126, 208, 255, .08);
        }

        .team-info {
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 0;
        }

        .team-logo-placeholder {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 6px;
            border: 1px solid rgba(134, 161, 190, .45);
            background: radial-gradient(circle at 50% 35%, rgba(116, 218, 255, .32), transparent 32%), #050a12;
            color: #75d9ff;
            font-size: 14px;
            font-weight: 900;
            box-shadow: inset 0 0 0 3px rgba(255,255,255,.03), 0 8px 16px rgba(0,0,0,.22);
        }

        .team-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: inherit;
            font-size: 20px;
            font-weight: 500;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .team-name.winner {
            color: #f3f8ff;
            font-weight: 800;
        }

        .score-info {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
        }

        .winner-badge {
            display: none;
        }

        .team-score {
            min-width: 22px;
            color: #6f7d91;
            font-size: 24px;
            font-weight: 800;
            text-align: right;
        }

        .team-score.winner {
            color: #81dcff;
        }

        .match-badge,
        .match-date-overlay {
            display: none;
        }

        .tarkam-fab {
            position: fixed;
            right: 42px;
            bottom: 100px;
            z-index: 50;
            width: 74px;
            height: 74px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(184, 231, 255, .5);
            border-radius: 22px;
            background: linear-gradient(180deg, #84d7ff, #63bee9);
            color: #062033;
            font-size: 42px;
            font-weight: 700;
            box-shadow: 0 18px 38px rgba(0,0,0,.32);
        }

        .tarkam-bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 45;
            height: 82px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-top: 1px solid rgba(154, 191, 224, .33);
            background: #071423;
        }

        .tarkam-bottom-nav::before {
            content: "";
            position: absolute;
            left: 58px;
            top: -1px;
            width: 43px;
            height: 5px;
            border-radius: 999px;
            background: #7fd8ff;
        }

        .tarkam-nav-item {
            display: grid;
            place-items: center;
            align-content: center;
            gap: 6px;
            color: #d8dfeb;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .tarkam-nav-item.is-active {
            color: #7fd8ff;
        }

        .tarkam-nav-icon {
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
        }

        .bracket-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: grid;
            place-items: center;
            background: rgba(3, 10, 18, .78);
            padding: 16px;
            backdrop-filter: blur(10px);
        }

        .bracket-modal-container {
            width: min(100%, 640px);
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(126, 208, 255, .28);
            background: #101b2b;
            color: #edf6ff;
            box-shadow: 0 28px 80px rgba(0,0,0,.5);
        }

        .bracket-modal-header,
        .bracket-modal-body {
            padding: 22px;
        }

        .bracket-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid rgba(126, 208, 255, .16);
            background: #142133;
        }

        .bracket-modal-label {
            margin: 0 0 6px;
            color: #7fd8ff;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .bracket-modal-title {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .bracket-modal-close-btn,
        .bracket-btn-cancel,
        .bracket-btn-submit,
        .bracket-btn-advance-bye {
            border-radius: 10px;
            border: 1px solid rgba(126, 208, 255, .25);
            padding: 10px 15px;
            background: #17263a;
            color: #edf6ff;
            font-weight: 800;
            cursor: pointer;
        }

        .bracket-modal-form-grid,
        .bracket-modal-input-group,
        .bracket-modal-meta-grid {
            display: grid;
            gap: 14px;
            margin-bottom: 18px;
        }

        .bracket-modal-input-group {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .bracket-modal-team-card {
            border-radius: 12px;
            border: 1px solid rgba(126, 208, 255, .16);
            background: #142133;
            padding: 15px;
        }

        .bracket-modal-team-name {
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 16px;
            font-weight: 800;
        }

        .bracket-score-section {
            margin-bottom: 18px;
        }

        .bracket-score-title {
            margin: 0 0 10px;
            color: #7fd8ff;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .bracket-modal-field {
            display: grid;
            gap: 7px;
            color: #aebed3;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .bracket-modal-input,
        .bracket-modal-select {
            width: 100%;
            border-radius: 10px;
            border: 1px solid rgba(126, 208, 255, .2);
            background: #081524;
            color: #edf6ff;
            padding: 11px 12px;
            outline: none;
        }

        .bracket-modal-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .bracket-btn-submit,
        .bracket-btn-advance-bye {
            background: #63bee9;
            color: #062033;
        }

        .bracket-modal-error {
            margin-bottom: 18px;
            border-radius: 10px;
            background: rgba(237, 63, 91, .14);
            color: #ffb9c1;
            padding: 12px 14px;
            font-weight: 700;
        }

        @media (min-width: 760px) {
            .bracket-modal-form-grid,
            .bracket-modal-meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 520px) {
            .tarkam-topbar {
                padding-inline: 24px;
            }

            .tarkam-logo {
                width: 58px;
                height: 58px;
                border-radius: 14px;
            }

            .tarkam-title {
                font-size: 24px;
            }

            .tarkam-round-tabs {
                gap: 18px;
                padding-left: 31px;
            }

            .tarkam-round-tab {
                padding-inline: 21px;
            }
        }
    </style>

    <header class="tarkam-topbar">
        <div class="tarkam-logo" aria-hidden="true">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
                <path d="M8 4h8v4a4 4 0 0 1-8 0V4Z" stroke="currentColor" stroke-width="1.8" />
                <path d="M8 6H5.5A2.5 2.5 0 0 0 8 10.5M16 6h2.5A2.5 2.5 0 0 1 16 10.5M12 12v5M8.5 20h7M10 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
            </svg>
        </div>
        <h1 class="tarkam-title">
            {{ $titleLead }} @if ($titleAccent !== '')<span>{{ $titleAccent }}</span>@endif
        </h1>
        <button type="button" class="tarkam-icon-button" aria-label="Search">
            <svg width="31" height="31" viewBox="0 0 24 24" fill="none">
                <circle cx="10.8" cy="10.8" r="6.5" stroke="currentColor" stroke-width="2" />
                <path d="m16 16 4.4 4.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </button>
    </header>

    <nav class="tarkam-round-tabs" aria-label="Bracket rounds">
        @foreach ($tournament->rounds as $round)
            <button type="button" class="tarkam-round-tab {{ $loop->first ? 'is-active' : '' }}">
                {{ strtoupper($round->name) }}
            </button>
        @endforeach
    </nav>

    <main
        class="tarkam-bracket-area"
        x-init="$nextTick(() => { $el.scrollLeft = 0; })"
    >
        <div
            class="tarkam-canvas"
            :style="'width: {{ $layout['canvasWidth'] }}px; height: {{ $layout['canvasHeight'] }}px; transform: scale(' + zoom + '); transform-origin: top left;'"
        >
            <div class="tarkam-stage-tick"></div>
            <div class="tarkam-stage-label">{{ strtoupper($firstRound?->name ?? 'Bracket') }}</div>

            <svg
                class="bracket-svg"
                width="{{ $layout['canvasWidth'] }}"
                height="{{ $layout['canvasHeight'] }}"
                viewBox="0 0 {{ $layout['canvasWidth'] }} {{ $layout['canvasHeight'] }}"
                fill="none"
            >
                @foreach ($layout['connectorPaths'] as $connector)
                    <path d="{{ $connector['d'] }}" />
                @endforeach
            </svg>

            @foreach ($tournament->rounds as $round)
                @foreach ($round->matches as $match)
                    @php
                        $position = $layout['matchPositions'][$match->id] ?? null;
                    @endphp

                    @if ($position)
                        <x-bracket.match-card
                            :match="$match"
                            :position="$position"
                            :cardWidth="$cardWidth"
                            :cardHeight="$cardHeight"
                            :isRight="$position['side'] === 'right'"
                            :labelNumber="$match->bracket_position"
                        />
                    @endif
                @endforeach
            @endforeach
        </div>
    </main>

    <button type="button" class="tarkam-fab" aria-label="Add match">+</button>

    <nav class="tarkam-bottom-nav" aria-label="Main navigation">
        <div class="tarkam-nav-item is-active"><span class="tarkam-nav-icon">BR</span><span>Bracket</span></div>
        <div class="tarkam-nav-item"><span class="tarkam-nav-icon">MT</span><span>Matches</span></div>
        <div class="tarkam-nav-item"><span class="tarkam-nav-icon">ST</span><span>Standings</span></div>
        <div class="tarkam-nav-item"><span class="tarkam-nav-icon">AD</span><span>Admin</span></div>
    </nav>

    @if ($selectedMatch)
        <x-bracket.match-modal :selectedMatch="$selectedMatch" :errors="$errors" />
    @endif
</div>
