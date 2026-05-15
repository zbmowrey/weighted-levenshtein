<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein;

if (!function_exists(__NAMESPACE__ . '\\lev')) {
    /**
     * Short alias for {@see Distance::levenshtein()}.
     */
    function lev(
        string $a,
        string $b,
        ?CharCostMap $insertCosts = null,
        ?CharCostMap $deleteCosts = null,
        ?CharPairCostMap $substituteCosts = null,
    ): float {
        return Distance::levenshtein($a, $b, $insertCosts, $deleteCosts, $substituteCosts);
    }
}

if (!function_exists(__NAMESPACE__ . '\\osa')) {
    /**
     * Short alias for {@see Distance::optimalStringAlignment()}.
     */
    function osa(
        string $a,
        string $b,
        ?CharCostMap $insertCosts = null,
        ?CharCostMap $deleteCosts = null,
        ?CharPairCostMap $substituteCosts = null,
        ?CharPairCostMap $transposeCosts = null,
    ): float {
        return Distance::optimalStringAlignment($a, $b, $insertCosts, $deleteCosts, $substituteCosts, $transposeCosts);
    }
}

if (!function_exists(__NAMESPACE__ . '\\dam_lev')) {
    /**
     * Short alias for {@see Distance::damerauLevenshtein()}.
     */
    function dam_lev(
        string $a,
        string $b,
        ?CharCostMap $insertCosts = null,
        ?CharCostMap $deleteCosts = null,
        ?CharPairCostMap $substituteCosts = null,
        ?CharPairCostMap $transposeCosts = null,
    ): float {
        return Distance::damerauLevenshtein($a, $b, $insertCosts, $deleteCosts, $substituteCosts, $transposeCosts);
    }
}
