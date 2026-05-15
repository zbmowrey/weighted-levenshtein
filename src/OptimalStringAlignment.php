<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein;

use Zbmowrey\WeightedLevenshtein\Internal\AsciiBytes;

/**
 * Optimal String Alignment distance — Wagner-Fischer with a single adjacent
 * transposition check (no overlap with other edits at the same substring).
 *
 * @see https://en.wikipedia.org/wiki/Damerau%E2%80%93Levenshtein_distance#Optimal_string_alignment_distance
 */
final class OptimalStringAlignment
{
    private const int ALPHABET_SIZE = 128;

    public static function compute(
        string $a,
        string $b,
        CharCostMap $insertCosts,
        CharCostMap $deleteCosts,
        CharPairCostMap $substituteCosts,
        CharPairCostMap $transposeCosts,
    ): float {
        $s1 = AsciiBytes::to1IndexedBytes($a, '$a');
        $s2 = AsciiBytes::to1IndexedBytes($b, '$b');
        $len1 = strlen($a);
        $len2 = strlen($b);

        $ic = $insertCosts->toArray();
        $dc = $deleteCosts->toArray();
        $sc = $substituteCosts->toFlatArray();
        $tc = $transposeCosts->toFlatArray();

        // Full (len1+1) x (len2+1) matrix, 0-indexed.
        $cols = $len2 + 1;
        $d = array_fill(0, ($len1 + 1) * $cols, 0.0);

        for ($i = 1; $i <= $len1; $i++) {
            $d[$i * $cols] = $d[($i - 1) * $cols] + $dc[$s1[$i]];
        }
        for ($j = 1; $j <= $len2; $j++) {
            $d[$j] = $d[$j - 1] + $ic[$s2[$j]];
        }

        for ($i = 1; $i <= $len1; $i++) {
            $charI = $s1[$i];
            $prevCharI = $i > 1 ? $s1[$i - 1] : 0;
            $rowI = $i * $cols;
            $rowIm1 = ($i - 1) * $cols;
            $rowIm2 = $i > 1 ? ($i - 2) * $cols : 0;
            $iRow = $charI * self::ALPHABET_SIZE;
            $prevIRow = $prevCharI * self::ALPHABET_SIZE;

            for ($j = 1; $j <= $len2; $j++) {
                $charJ = $s2[$j];
                if ($charI === $charJ) {
                    $value = $d[$rowIm1 + $j - 1];
                } else {
                    $del = $d[$rowIm1 + $j] + $dc[$charI];
                    $ins = $d[$rowI + $j - 1] + $ic[$charJ];
                    $sub = $d[$rowIm1 + $j - 1] + $sc[$iRow + $charJ];
                    $value = $del < $ins ? ($del < $sub ? $del : $sub) : ($ins < $sub ? $ins : $sub);
                }

                if ($i > 1 && $j > 1) {
                    $prevCharJ = $s2[$j - 1];
                    if ($charI === $prevCharJ && $prevCharI === $charJ) {
                        $trans = $d[$rowIm2 + $j - 2] + $tc[$prevIRow + $charI];
                        if ($trans < $value) {
                            $value = $trans;
                        }
                    }
                }

                $d[$rowI + $j] = $value;
            }
        }

        return $d[$len1 * $cols + $len2];
    }
}
