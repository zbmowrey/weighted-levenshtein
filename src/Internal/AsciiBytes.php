<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Internal;

use InvalidArgumentException;

/**
 * @internal
 */
final class AsciiBytes
{
    /**
     * Convert an ASCII string to a 1-indexed array of byte values.
     *
     * Index 0 is unused (set to 0); indices 1..strlen($s) hold the byte values.
     * This mirrors the 1-indexed access pattern used by the reference Cython
     * implementation and the Wagner-Fischer formulation.
     *
     * @return list<int>
     *
     * @throws InvalidArgumentException if $s contains any byte >= 128.
     */
    public static function to1IndexedBytes(string $s, string $argumentName): array
    {
        $len = strlen($s);
        $out = [0];
        for ($i = 0; $i < $len; $i++) {
            $code = ord($s[$i]);
            if ($code > 127) {
                throw new InvalidArgumentException(
                    $argumentName . ' must be an ASCII string (bytes 0–127); '
                    . 'found byte ' . $code . ' at index ' . $i . '.',
                );
            }
            $out[] = $code;
        }

        return $out;
    }
}
