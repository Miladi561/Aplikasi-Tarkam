<?php

namespace App\Services\Bracket;

class BracketCoordinateService
{
    public const CARD_WIDTH = 366;
    public const CARD_HEIGHT = 208;
    public const BASE_SPACING = 246;
    public const ROUND_SPACING = 450;
    public const BOARD_PADDING = 31;
    public const HEADER_HEIGHT = 118;

    /**
     * Get the position for a match.
     *
     * @param string $side
     * @param int $roundIndex
     * @param int $matchIndex
     * @param int $canvasWidth
     * @param int $bracketHeight
     * @param int $canvasHeight
     * @return array{side:string, roundIndex:int, matchIndex:int, left:int, top:int, centerX:int, centerY:int}
     */
    public function getMatchPosition(
        string $side,
        int $roundIndex,
        int $matchIndex,
        int $canvasWidth,
        int $bracketHeight,
        int $canvasHeight
    ): array {
        $baseSpacing = self::BASE_SPACING;
        $roundSpacing = self::ROUND_SPACING;
        $cardWidth = self::CARD_WIDTH;
        $cardHeight = self::CARD_HEIGHT;
        $boardPadding = self::BOARD_PADDING;
        $headerHeight = self::HEADER_HEIGHT;

        if ($side === 'final') {
            // FINAL CENTER
            $topY = ($canvasHeight / 2) - ($cardHeight / 2);
            $left = ($canvasWidth / 2) - ($cardWidth / 2);
        } else {
            // FIX FORMULA Y POSITION
            $slotSpacing = $baseSpacing * pow(2, $roundIndex);

            $top = ($matchIndex * $slotSpacing)
                + ($slotSpacing / 2)
                - ($cardHeight / 2);

            $topY = $headerHeight + $top;



            // FORMULA X POSITION
            if ($side === 'right') {
                $left = $canvasWidth - $boardPadding - $cardWidth - ($roundIndex * $roundSpacing);
            } else {
                $left = $boardPadding + ($roundIndex * $roundSpacing);
            }
        }

        return [
            'side' => $side,
            'roundIndex' => $roundIndex,
            'matchIndex' => $matchIndex,
            'left' => (int) $left,
            'top' => (int) $topY,
            'centerX' => (int) ($left + ($cardWidth / 2)),
            'centerY' => (int) ($topY + ($cardHeight / 2)),
        ];
    }

    /**
     * Get the SVG connector path coordinates between two match positions.
     *
     * @param array{side:string, roundIndex:int, matchIndex:int, left:int, top:int, centerX:int, centerY:int} $from
     * @param array{side:string, roundIndex:int, matchIndex:int, left:int, top:int, centerX:int, centerY:int} $to
     * @param int $cardWidth
     * @return string
     */
    public function getConnectorCoordinates(array $from, array $to, int $cardWidth): string
    {
        $side = $from['side'];

        if ($side === 'right') {
            $startX = $from['left'];
            $endX = $to['left'] + $cardWidth;
            $joinX = $startX - 58;
        } else {
            $startX = $from['left'] + $cardWidth;
            $endX = $to['left'];
            $joinX = $startX + 42;
        }

        $startY = $from['centerY'];
        $endY = $to['centerY'];

        return "M {$startX} {$startY} H {$joinX} V {$endY} H {$endX}";
    }

    /**
     * Get height for a specific round based on matches count.
     *
     * @param int $roundIndex
     * @param int $matchesInRound
     * @return int
     */
    public function getRoundHeight(int $roundIndex, int $matchesInRound): int
    {
        $slotHeight = self::BASE_SPACING * pow(2, $roundIndex);
        return $matchesInRound * $slotHeight;
    }
}
