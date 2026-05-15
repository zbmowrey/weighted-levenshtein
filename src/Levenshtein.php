<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein;

use Zbmowrey\WeightedLevenshtein\Internal\AsciiBytes;

/**
 * Weighted Levenshtein distance via Wagner-Fischer.
 *
 * @see https://en.wikipedia.org/wiki/Wagner%E2%80%93Fischer_algorithm
 */
final class Levenshtein
{
    private const int ALPHABET_SIZE = 128;

    public static function compute(
        string $a,
        string $b,
        CharCostMap $insertCosts,
        CharCostMap $deleteCosts,
        CharPairCostMap $substituteCosts,
    ): float {
        $s1 = AsciiBytes::to1IndexedBytes($a, '$a');
        $s2 = AsciiBytes::to1IndexedBytes($b, '$b');
        $len1 = strlen($a);
        $len2 = strlen($b);

        $ic = $insertCosts->toArray();
        $dc = $deleteCosts->toArray();
        $sc = $substituteCosts->toFlatArray();

        // Two-row rolling buffer. prev = row (i-1), curr = row i.
        $prev = array_fill(0, $len2 + 1, 0.0);
        for ($j = 1; $j <= $len2; $j++) {
            $prev[$j] = $prev[$j - 1] + $ic[$s2[$j]];
        }

        $curr = array_fill(0, $len2 + 1, 0.0);
        for ($i = 1; $i <= $len1; $i++) {
            $charI = $s1[$i];
            $curr[0] = $prev[0] + $dc[$charI];
            $iRow = $charI * self::ALPHABET_SIZE;

            for ($j = 1; $j <= $len2; $j++) {
                $charJ = $s2[$j];
                if ($charI === $charJ) {
                    $curr[$j] = $prev[$j - 1];
                } else {
                    $del = $prev[$j] + $dc[$charI];
                    $ins = $curr[$j - 1] + $ic[$charJ];
                    $sub = $prev[$j - 1] + $sc[$iRow + $charJ];
                    $curr[$j] = $del < $ins ? ($del < $sub ? $del : $sub) : ($ins < $sub ? $ins : $sub);
                }
            }

            [$prev, $curr] = [$curr, $prev];
        }

        return $prev[$len2];
    }
}
