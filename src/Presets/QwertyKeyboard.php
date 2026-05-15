<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Presets;

use Zbmowrey\WeightedLevenshtein\CharPairCostMap;

/**
 * Cost-map presets derived from the physical layout of a standard US QWERTY
 * keyboard. Useful for human-typo correction where the likelihood of an error
 * correlates with how close two keys sit to each other.
 *
 * Coordinates use the staggered layout: each row is offset slightly to the
 * right of the row above. Same-row horizontal neighbors are at distance 1.0;
 * diagonal neighbors are at roughly distance 1.0–1.3.
 *
 * Both lowercase and uppercase letter pairs are populated. Digit-row pairs
 * are populated for the unshifted digit characters. Mixed case (e.g. `q`/`W`)
 * and mixed shift-state (e.g. `Q`/`1`) pairs are intentionally left at the
 * default cost — those errors are rare in practice.
 */
final class QwertyKeyboard
{
    /**
     * Key coordinates: char => [x, y]. PHP coerces numeric-string keys to int,
     * so the digit characters are stored under int keys; we cast back to
     * string when iterating.
     *
     * @var array<int|string, array{0: float, 1: float}>
     */
    private const array LAYOUT = [
        // Row 0 — digits
        '1' => [0.0, 0.0], '2' => [1.0, 0.0], '3' => [2.0, 0.0], '4' => [3.0, 0.0],
        '5' => [4.0, 0.0], '6' => [5.0, 0.0], '7' => [6.0, 0.0], '8' => [7.0, 0.0],
        '9' => [8.0, 0.0], '0' => [9.0, 0.0],
        // Row 1 — top letter row (offset 0.5)
        'q' => [0.5, 1.0], 'w' => [1.5, 1.0], 'e' => [2.5, 1.0], 'r' => [3.5, 1.0],
        't' => [4.5, 1.0], 'y' => [5.5, 1.0], 'u' => [6.5, 1.0], 'i' => [7.5, 1.0],
        'o' => [8.5, 1.0], 'p' => [9.5, 1.0],
        // Row 2 — home row (offset 0.75)
        'a' => [0.75, 2.0], 's' => [1.75, 2.0], 'd' => [2.75, 2.0], 'f' => [3.75, 2.0],
        'g' => [4.75, 2.0], 'h' => [5.75, 2.0], 'j' => [6.75, 2.0], 'k' => [7.75, 2.0],
        'l' => [8.75, 2.0],
        // Row 3 — bottom letter row (offset 1.25)
        'z' => [1.25, 3.0], 'x' => [2.25, 3.0], 'c' => [3.25, 3.0], 'v' => [4.25, 3.0],
        'b' => [5.25, 3.0], 'n' => [6.25, 3.0], 'm' => [7.25, 3.0],
    ];

    /**
     * Adjacency-weighted substitution cost map.
     *
     * Pairs of keys whose Euclidean distance is below 1.5 (orthogonal or
     * diagonal neighbors) are weighted at $adjacentCost. Pairs at distance
     * below 2.5 (one key removed) are weighted at $nearCost. Everything
     * further out retains the default cost of 1.0.
     */
    public static function substituteCosts(float $adjacentCost = 0.5, float $nearCost = 0.75): CharPairCostMap
    {
        return self::buildAdjacencyMap($adjacentCost, $nearCost);
    }

    /**
     * Adjacency-weighted transposition cost map. Reuses the same layout-based
     * heuristic: adjacent keys are easy to swap because they are typed by
     * neighboring fingers.
     */
    public static function transposeCosts(float $adjacentCost = 0.5, float $nearCost = 0.75): CharPairCostMap
    {
        return self::buildAdjacencyMap($adjacentCost, $nearCost);
    }

    private static function buildAdjacencyMap(float $adjacentCost, float $nearCost): CharPairCostMap
    {
        $map = CharPairCostMap::uniform();
        foreach (self::LAYOUT as $aKey => [$ax, $ay]) {
            $a = (string) $aKey;
            foreach (self::LAYOUT as $bKey => [$bx, $by]) {
                $b = (string) $bKey;
                if ($a === $b) {
                    continue;
                }
                $dx = $ax - $bx;
                $dy = $ay - $by;
                $distance = sqrt($dx * $dx + $dy * $dy);
                $cost = match (true) {
                    $distance < 1.5 => $adjacentCost,
                    $distance < 2.5 => $nearCost,
                    default => null,
                };
                if ($cost === null) {
                    continue;
                }
                $map = $map->withCost($a, $b, $cost);
                if (ctype_alpha($a) && ctype_alpha($b)) {
                    $map = $map->withCost(strtoupper($a), strtoupper($b), $cost);
                }
            }
        }

        return $map;
    }
}
