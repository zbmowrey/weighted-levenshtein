<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein;

/**
 * Public facade for weighted edit-distance algorithms.
 *
 * Each method accepts two ASCII strings and optional per-character or
 * per-ordered-pair cost maps. Passing null for a cost map is equivalent
 * to a uniform cost of 1.0.
 *
 * All inputs must be ASCII (bytes 0–127). Non-ASCII bytes raise
 * {@see \InvalidArgumentException}.
 */
final class Distance
{
    public static function levenshtein(
        string $a,
        string $b,
        ?CharCostMap $insertCosts = null,
        ?CharCostMap $deleteCosts = null,
        ?CharPairCostMap $substituteCosts = null,
    ): float {
        return Levenshtein::compute(
            $a,
            $b,
            $insertCosts ?? CharCostMap::uniform(),
            $deleteCosts ?? CharCostMap::uniform(),
            $substituteCosts ?? CharPairCostMap::uniform(),
        );
    }

    public static function optimalStringAlignment(
        string $a,
        string $b,
        ?CharCostMap $insertCosts = null,
        ?CharCostMap $deleteCosts = null,
        ?CharPairCostMap $substituteCosts = null,
        ?CharPairCostMap $transposeCosts = null,
    ): float {
        return OptimalStringAlignment::compute(
            $a,
            $b,
            $insertCosts ?? CharCostMap::uniform(),
            $deleteCosts ?? CharCostMap::uniform(),
            $substituteCosts ?? CharPairCostMap::uniform(),
            $transposeCosts ?? CharPairCostMap::uniform(),
        );
    }

    public static function damerauLevenshtein(
        string $a,
        string $b,
        ?CharCostMap $insertCosts = null,
        ?CharCostMap $deleteCosts = null,
        ?CharPairCostMap $substituteCosts = null,
        ?CharPairCostMap $transposeCosts = null,
    ): float {
        return DamerauLevenshtein::compute(
            $a,
            $b,
            $insertCosts ?? CharCostMap::uniform(),
            $deleteCosts ?? CharCostMap::uniform(),
            $substituteCosts ?? CharPairCostMap::uniform(),
            $transposeCosts ?? CharPairCostMap::uniform(),
        );
    }
}
