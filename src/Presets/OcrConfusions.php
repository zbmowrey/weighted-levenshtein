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
     * The list is intentionally conservative — only pairs that confuse across
     * most fonts are included. Font-specific or low-resolution-only confusions
     * (e.g. 4↔A, 0↔6, n↔u) are intentionally omitted; layer those on with
     * {@see CharPairCostMap::withCost()} if your input data needs them.
     *
     * Multi-character confusions (rn→m, cl→d, vv→w) cannot be expressed in a
     * single-character substitution map and are documented but skipped.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const array CONFUSIONS = [
        // Zero / O / o
        ['0', 'O'],
        ['0', 'o'],
        ['O', 'o'],
        ['0', 'D'],
        ['O', 'D'],
        ['O', 'Q'],
        // One / l / I / i
        ['1', 'l'],
        ['1', 'I'],
        ['1', 'i'],
        ['l', 'I'],
        ['l', 'i'],
        ['I', 'i'],
        // Seven — continental crossbar-less seven and angular cousins
        ['1', '7'],
        ['2', '7'],
        ['7', 'T'],
        ['7', 'Z'],
        ['7', 'z'],
        // Other digit / digit
        ['2', 'Z'],
        ['2', 'z'],
        ['3', '5'],
        ['3', '8'],
        // Other digit / letter
        ['5', 'S'],
        ['5', 's'],
        ['8', 'B'],
        ['6', 'G'],
        ['6', 'b'],
        ['9', 'g'],
        ['9', 'q'],
        // Letter / letter — lowercase
        ['c', 'e'],
        ['n', 'h'],
        ['u', 'v'],
        ['m', 'n'],
        ['f', 't'],
        ['r', 'n'],
        // Letter / letter — uppercase
        ['C', 'G'],
        ['E', 'F'],
        ['M', 'N'],
        ['P', 'R'],
        ['U', 'V'],
        ['V', 'Y'],
        // Multi-character confusions (documented; skipped at runtime — see filter in common()).
        ['rn', 'm'],
        ['cl', 'd'],
        ['vv', 'w'],
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
