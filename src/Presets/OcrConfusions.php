<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Presets;

use Zbmowrey\WeightedLevenshtein\CharPairCostMap;

/**
 * Substitution-cost presets for OCR output where visually similar characters
 * are frequently confused (e.g. zero with capital O, lowercase l with digit 1).
 *
 * The curated list is intentionally conservative — high-confidence confusions
 * only. Multi-character confusions (rn → m, cl → d) cannot be expressed in a
 * single-character substitution map and are omitted.
 *
 * The returned map is immutable; use {@see CharPairCostMap::withCost()} to
 * layer domain-specific overrides on top.
 */
final class OcrConfusions
{
    /**
     * Curated set of high-confidence OCR confusions. Each pair is registered
     * in both directions because OCR confusion is naturally symmetric.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const array CONFUSIONS = [
        // Zero / capital-O / lowercase-o
        ['0', 'O'],
        ['0', 'o'],
        ['O', 'o'],
        // One / lowercase-l / uppercase-I / lowercase-i
        ['1', 'l'],
        ['1', 'I'],
        ['1', 'i'],
        ['l', 'I'],
        ['l', 'i'],
        ['I', 'i'],
        // Digit / letter visual lookalikes
        ['5', 'S'],
        ['5', 's'],
        ['8', 'B'],
        ['2', 'Z'],
        ['2', 'z'],
        ['6', 'G'],
        ['6', 'b'],
        ['9', 'g'],
        ['9', 'q'],
        // Letter / letter visual lookalikes (small)
        ['c', 'e'],
        ['n', 'h'],
        ['u', 'v'],
        ['m', 'n'],
        ['rn', 'm'], // omitted at runtime — kept here for documentation; see filter below.
    ];

    /**
     * Returns a {@see CharPairCostMap} where each curated OCR confusion has
     * substitution cost $cost, in both directions. All other pairs retain
     * the default cost of 1.0.
     *
     * Identity pairs (e.g. `('A', 'A')`) are not affected, since the algorithms
     * treat character matches as a free operation.
     */
    public static function common(float $cost = 0.25): CharPairCostMap
    {
        $map = CharPairCostMap::uniform();
        foreach (self::CONFUSIONS as [$a, $b]) {
            if (strlen($a) !== 1 || strlen($b) !== 1) {
                continue;
            }
            $map = $map->withCost($a, $b, $cost)->withCost($b, $a, $cost);
        }

        return $map;
    }
}
