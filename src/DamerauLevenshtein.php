<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein;

use Zbmowrey\WeightedLevenshtein\Internal\AsciiBytes;

/**
 * True Damerau-Levenshtein distance — allows arbitrary numbers of edits
 * including non-overlapping adjacent transpositions.
 *
 * @see https://en.wikipedia.org/wiki/Damerau%E2%80%93Levenshtein_distance#Distance_with_adjacent_transpositions
 */
final class DamerauLevenshtein
{
    private const int ALPHABET_SIZE = 128;

    /**
     * Sentinel "infinity" for the boundary rows/columns. Large enough to
     * dominate any realistic cost combination, but well below PHP_FLOAT_MAX
     * so additions never overflow.
     */
    private const float INFINITY = 1.0e17;

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

        // The reference uses a (-1)-indexed matrix of size (len1+2) x (len2+2)
        // with row -1 and column -1 filled with infinity. We shift to a
        // 0-indexed flat array where logical (row, col) maps to physical
        // (row + 1, col + 1). The first physical row/col is the infinity boundary.
        $rows = $len1 + 2;
        $cols = $len2 + 2;
        $d = array_fill(0, $rows * $cols, self::INFINITY);

        // d[0][0] (logical -1,-1) is infinity (already set).
        // d[1][1] (logical 0,0) is 0.
        $d[1 * $cols + 1] = 0.0;

        // d[i+1][1] (logical i,0) = cumulative delete costs for str1[1..i].
        for ($i = 1; $i <= $len1; $i++) {
            $d[($i + 1) * $cols + 1] = $d[$i * $cols + 1] + $dc[$s1[$i]];
        }
        // d[1][j+1] (logical 0,j) = cumulative insert costs for str2[1..j].
        for ($j = 1; $j <= $len2; $j++) {
            $d[$cols + ($j + 1)] = $d[$cols + $j] + $ic[$s2[$j]];
        }

        // da[c] = last position in str1 where character c appeared; 0 if never.
        $da = array_fill(0, self::ALPHABET_SIZE, 0);

        for ($i = 1; $i <= $len1; $i++) {
            $charI = $s1[$i];
            $iRow = $charI * self::ALPHABET_SIZE;
            $rowI = ($i + 1) * $cols; // physical row for logical i

            $db = 0;
            for ($j = 1; $j <= $len2; $j++) {
                $charJ = $s2[$j];
                $k = $da[$charJ];
                $l = $db;
                if ($charI === $charJ) {
                    $cost = 0.0;
                    $db = $j;
                } else {
                    $cost = $sc[$iRow + $charJ];
                }

                // Match/substitute, insert, delete.
                $sub = $d[$i * $cols + $j] + $cost;          // d[i-1][j-1]
                $ins = $d[($i + 1) * $cols + $j] + $ic[$charJ]; // d[i][j-1]
                $del = $d[$i * $cols + ($j + 1)] + $dc[$charI]; // d[i-1][j]
                $best = $sub < $ins ? ($sub < $del ? $sub : $del) : ($ins < $del ? $ins : $del);

                // Transpose. d[k-1][l-1] + (col delete range k+1..i-1) +
                // transpose_costs[str1[k], str1[i]] + (row insert range l+1..j-1).
                // Logical (r, c) -> physical (r+1, c+1).
                $base = $d[$k * $cols + $l];
                // col_delete_range = d_logical[i-1][0] - d_logical[k][0]
                //                  = d_physical[i][1] - d_physical[k+1][1]
                $colRange = $d[$i * $cols + 1] - $d[($k + 1) * $cols + 1];
                // row_insert_range = d_logical[0][j-1] - d_logical[0][l]
                //                  = d_physical[1][j] - d_physical[1][l+1]
                $rowRange = $d[$cols + $j] - $d[$cols + ($l + 1)];
                $transposeChar = $k > 0 ? $s1[$k] : 0;
                $trans = $base + $colRange + $tc[$transposeChar * self::ALPHABET_SIZE + $charI] + $rowRange;
                if ($trans < $best) {
                    $best = $trans;
                }

                $d[$rowI + ($j + 1)] = $best;
            }

            $da[$charI] = $i;
        }

        return $d[($len1 + 1) * $cols + ($len2 + 1)];
    }
}
