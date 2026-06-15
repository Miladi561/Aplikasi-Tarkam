<?php

namespace App\Services\Bracket;

use App\Models\Tournament;
use Illuminate\Support\Collection;

class BracketPositionService
{
    public const CARD_WIDTH = BracketCoordinateService::CARD_WIDTH;

    public const CARD_HEIGHT = BracketCoordinateService::CARD_HEIGHT;

    public const ROUND_SPACING = BracketCoordinateService::ROUND_SPACING;

    public const BASE_SPACING = BracketCoordinateService::BASE_SPACING;

    public const BOARD_PADDING = BracketCoordinateService::BOARD_PADDING;

    public const HEADER_HEIGHT = BracketCoordinateService::HEADER_HEIGHT;

    public const CENTER_GAP = 220;

    protected BracketCoordinateService $coordinateService;

    public function __construct(BracketCoordinateService $coordinateService)
    {
        $this->coordinateService = $coordinateService;
    }

    /**
     * @return array{
     *     canvasWidth:int,
     *     canvasHeight:int,
     *     centerX:int,
     *     bracketTop:int,
     *     bracketHeight:int,
     *     matchPositions:array<int, array{side:string, roundIndex:int, matchIndex:int, left:int, top:int, centerX:int, centerY:int}>,
     *     connectorPaths:array<int, array{side:string, from:int, to:int, d:string}>,
     *     final:array{left:int, top:int, centerX:int, centerY:int}|null
     * }
     */
    public function build(Tournament $tournament): array
    {
        $rounds = $tournament->rounds->values();
        $roundCount = max(1, $rounds->count());
        $sideRoundCount = max(1, $roundCount - 1);
        $firstRoundMatches = $rounds->first()?->matches ?? collect();
        [$firstLeft, $firstRight] = $this->splitRoundMatches($firstRoundMatches);
        $virtualBaseSlots = $this->nextPowerOfTwo(max(1, $firstLeft->count(), $firstRight->count()));
        $bracketHeight = $virtualBaseSlots * self::BASE_SPACING;
        $canvasHeight = self::HEADER_HEIGHT + $bracketHeight + self::BOARD_PADDING * 2;
        $centerX = self::BOARD_PADDING + ($sideRoundCount * self::ROUND_SPACING) + self::CENTER_GAP;
        $canvasWidth = ($centerX * 2);
        $matchPositions = [];

        foreach ($rounds as $roundIndex => $round) {
            $matches = $round->matches->values();

            if ($roundIndex === $roundCount - 1) {
                foreach ($matches as $match) {
                    $position = $this->coordinateService->getMatchPosition(
                        side: 'final',
                        roundIndex: (int) $roundIndex,
                        matchIndex: 0,
                        canvasWidth: $canvasWidth,
                        bracketHeight: $bracketHeight,
                        canvasHeight: $canvasHeight
                    );

                    $matchPositions[$match->id] = $position;
                }

                continue;
            }

            [$leftMatches, $rightMatches] = $this->splitRoundMatches($matches);

            foreach ($leftMatches->values() as $matchIndex => $match) {
                $position = $this->coordinateService->getMatchPosition(
                    side: 'left',
                    roundIndex: (int) $roundIndex,
                    matchIndex: (int) $matchIndex,
                    canvasWidth: $canvasWidth,
                    bracketHeight: $bracketHeight,
                    canvasHeight: $canvasHeight
                );

                $matchPositions[$match->id] = $position;
            }

            foreach ($rightMatches->values() as $matchIndex => $match) {
                $position = $this->coordinateService->getMatchPosition(
                    side: 'right',
                    roundIndex: (int) $roundIndex,
                    matchIndex: (int) $matchIndex,
                    canvasWidth: $canvasWidth,
                    bracketHeight: $bracketHeight,
                    canvasHeight: $canvasHeight
                );

                $matchPositions[$match->id] = $position;
            }
        }

        return [
            'canvasWidth' => $canvasWidth,
            'canvasHeight' => $canvasHeight,
            'centerX' => $centerX,
            'bracketTop' => self::HEADER_HEIGHT,
            'bracketHeight' => $bracketHeight,
            'matchPositions' => $matchPositions,
            'connectorPaths' => $this->buildConnectorPaths($tournament, $matchPositions),
            'final' => $this->finalPosition($rounds, $matchPositions),
        ];
    }

    /**
     * @param Collection<int, mixed> $matches
     * @return array{0:Collection<int, mixed>, 1:Collection<int, mixed>}
     */
    private function splitRoundMatches(Collection $matches): array
    {
        $leftCount = (int) ceil($matches->count() / 2);

        $leftMatches  = $matches->take($leftCount)->values();
        $rightMatches = $matches->slice($leftCount)->values(); // HAPUS ->reverse()

        return [$leftMatches, $rightMatches];
    }

    /**
     * @param array<int, array{side:string, roundIndex:int, matchIndex:int, left:int, top:int, centerX:int, centerY:int}> $matchPositions
     * @return array<int, array{side:string, from:int, to:int, d:string}>
     */
    private function buildConnectorPaths(Tournament $tournament, array $matchPositions): array
    {
        return $tournament->rounds
            ->flatMap(fn($round): Collection => $round->matches)
            ->filter(fn($match): bool => $match->next_match_id !== null && isset($matchPositions[$match->id], $matchPositions[$match->next_match_id]))
            ->values()
            ->map(function ($match) use ($matchPositions): array {
                $from = $matchPositions[$match->id];
                $to = $matchPositions[$match->next_match_id];
                $side = $from['side'];

                $d = $this->coordinateService->getConnectorCoordinates($from, $to, self::CARD_WIDTH);

                return [
                    'side' => $side,
                    'from' => (int) $match->id,
                    'to' => (int) $match->next_match_id,
                    'd' => $d,
                ];
            })
            ->all();
    }

    private function nextPowerOfTwo(int $value): int
    {
        $power = 1;

        while ($power < $value) {
            $power *= 2;
        }

        return $power;
    }

    /**
     * @param Collection<int, mixed> $rounds
     * @param array<int, array{side:string, roundIndex:int, matchIndex:int, left:int, top:int, centerX:int, centerY:int}> $matchPositions
     * @return array{left:int, top:int, centerX:int, centerY:int}|null
     */
    private function finalPosition(Collection $rounds, array $matchPositions): ?array
    {
        $finalMatch = $rounds->last()?->matches->first();

        if (! $finalMatch || ! isset($matchPositions[$finalMatch->id])) {
            return null;
        }

        return [
            'left' => $matchPositions[$finalMatch->id]['left'],
            'top' => $matchPositions[$finalMatch->id]['top'],
            'centerX' => $matchPositions[$finalMatch->id]['centerX'],
            'centerY' => $matchPositions[$finalMatch->id]['centerY'],
        ];
    }
}
