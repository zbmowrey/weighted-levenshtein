# weighted-levenshtein

[![CI](https://github.com/zbmowrey/weighted-levenshtein/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/zbmowrey/weighted-levenshtein/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Weighted Levenshtein, Optimal String Alignment, and Damerau-Levenshtein edit-distance algorithms for PHP 8.4+. Pure PHP, strict types, no extensions.

PHP port of [weighted-levenshtein](https://github.com/infoscout/weighted-levenshtein) by David Su / InfoScout. The algorithms, default behavior, and asymmetric cost semantics are preserved exactly; only the API has been reshaped to feel native in modern PHP.

## Why weighted distance?

Most edit-distance libraries treat every insertion, deletion, substitution, or transposition as cost 1. That's fine for generic fuzzy-matching but often not what you actually want.

- **OCR correction.** Substituting `0` for `O` is a likely confusion; substituting `X` for `O` is not. Give the first a smaller cost.
- **Typo correction.** On a QWERTY keyboard, `X` and `Z` are neighbors; `X` and `K` are not. Or: humans transpose adjacent letters frequently, so transpositions should be cheap.
- **Domain-specific noise.** If your input pipeline tends to drop trailing whitespace or duplicate digits, weight those operations accordingly.

This library lets you specify a cost per character (for insert/delete) and per ordered pair (for substitute/transpose), then runs the appropriate dynamic-programming algorithm.

## Installation

```bash
composer require zbmowrey/weighted-levenshtein
```

Requires PHP 8.4 or newer.

## Quick start

```php
use Zbmowrey\WeightedLevenshtein\Distance;

// Default (uniform) costs of 1.0 per operation.
echo Distance::levenshtein('kitten', 'sitting');             // 3
echo Distance::optimalStringAlignment('ca', 'ac');           // 1 (one transposition)
echo Distance::damerauLevenshtein('ab', 'bca');              // 2
```

Cost maps are immutable value objects. Build them with `withCost()`:

```php
use Zbmowrey\WeightedLevenshtein\CharCostMap;
use Zbmowrey\WeightedLevenshtein\Distance;

$insertCosts = CharCostMap::uniform()->withCost('D', 1.5);
echo Distance::levenshtein('BANANAS', 'BANDANAS', $insertCosts);  // 1.5
```

## Full examples

These are the same examples as the original Python README, translated 1:1. Every snippet has a corresponding test in `tests/Readme/`.

```php
use Zbmowrey\WeightedLevenshtein\CharCostMap;
use Zbmowrey\WeightedLevenshtein\CharPairCostMap;
use Zbmowrey\WeightedLevenshtein\Distance;

// --- Insertion cost ---
$insertCosts = CharCostMap::uniform()->withCost('D', 1.5);
echo Distance::levenshtein('BANANAS', 'BANDANAS', $insertCosts);
// 1.5

// --- Deletion cost ---
$deleteCosts = CharCostMap::uniform()->withCost('S', 0.5);
echo Distance::levenshtein('BANANAS', 'BANANA', $insertCosts, $deleteCosts);
// 0.5

// --- Substitution cost (asymmetric!) ---
$subs = CharPairCostMap::uniform()->withCost('H', 'B', 1.25);
echo Distance::levenshtein('HANANA', 'BANANA', null, null, $subs);
// 1.25

// The reverse direction is unweighted because we never set ('B','H').
echo Distance::levenshtein('BANANA', 'HANANA', null, null, $subs);
// 1.0

// Make the reverse direction match by setting the other ordered pair.
$subs = $subs->withCost('B', 'H', 1.25);
echo Distance::levenshtein('BANANA', 'HANANA', null, null, $subs);
// 1.25

// --- Transposition cost (Damerau-Levenshtein) ---
$transposes = CharPairCostMap::uniform()->withCost('A', 'B', 0.75);
echo Distance::damerauLevenshtein('ABNANA', 'BANANA', null, null, null, $transposes);
// 0.75

// Like substitution, transposition is also asymmetric.
echo Distance::damerauLevenshtein('BANANA', 'ABNANA', null, null, null, $transposes);
// 1.0

// Set the other direction to make it symmetric.
$transposes = $transposes->withCost('B', 'A', 0.75);
echo Distance::damerauLevenshtein('BANANA', 'ABNANA', null, null, null, $transposes);
// 0.75
```

Short aliases are available as free functions:

```php
use function Zbmowrey\WeightedLevenshtein\{lev, osa, dam_lev};

echo lev('kitten', 'sitting');     // 3
echo osa('ca', 'ac');              // 1
echo dam_lev('ab', 'ba');          // 1
```

## API reference

### `Zbmowrey\WeightedLevenshtein\Distance`

| Method | Description |
| --- | --- |
| `Distance::levenshtein(string $a, string $b, ?CharCostMap $insertCosts = null, ?CharCostMap $deleteCosts = null, ?CharPairCostMap $substituteCosts = null): float` | Wagner-Fischer Levenshtein distance. |
| `Distance::optimalStringAlignment(string $a, string $b, ?CharCostMap $insertCosts = null, ?CharCostMap $deleteCosts = null, ?CharPairCostMap $substituteCosts = null, ?CharPairCostMap $transposeCosts = null): float` | Wagner-Fischer with a single adjacent transposition check; substrings used in a transposition cannot also be edited. |
| `Distance::damerauLevenshtein(string $a, string $b, ?CharCostMap $insertCosts = null, ?CharCostMap $deleteCosts = null, ?CharPairCostMap $substituteCosts = null, ?CharPairCostMap $transposeCosts = null): float` | True Damerau-Levenshtein distance with arbitrary non-overlapping adjacent transpositions. |

### `Zbmowrey\WeightedLevenshtein\CharCostMap`

Immutable per-character cost map for insert/delete operations.

| Method | Description |
| --- | --- |
| `CharCostMap::uniform(float $defaultCost = 1.0): self` | Construct a map where every character has cost `$defaultCost`. |
| `withCost(string $char, float $cost): self` | Return a new map with `$cost` for the single ASCII byte `$char`. |
| `cost(string $char): float` | Look up the cost for `$char`. |

### `Zbmowrey\WeightedLevenshtein\CharPairCostMap`

Immutable per-ordered-pair cost map for substitute/transpose operations.

| Method | Description |
| --- | --- |
| `CharPairCostMap::uniform(float $defaultCost = 1.0): self` | Construct a map where every ordered pair has cost `$defaultCost`. |
| `withCost(string $from, string $to, float $cost): self` | Return a new map with `$cost` for the ordered pair (`$from`, `$to`). |
| `cost(string $from, string $to): float` | Look up the cost for the ordered pair. |

### Free function aliases

In namespace `Zbmowrey\WeightedLevenshtein`:

- `lev(...)` — alias for `Distance::levenshtein(...)`.
- `osa(...)` — alias for `Distance::optimalStringAlignment(...)`.
- `dam_lev(...)` — alias for `Distance::damerauLevenshtein(...)`.

## Limitations

- **ASCII only.** Inputs must consist of bytes 0–127. Any byte ≥ 128 raises `InvalidArgumentException`. This matches the original library, whose internal arrays are indexed by `ord()` over a 128-wide alphabet.
- **Case sensitive.** `'a'` and `'A'` are distinct characters.
- **Costs are asymmetric.** `CharPairCostMap` is keyed by *ordered* pairs. Setting `('A', 'B')` does not set `('B', 'A')`. If you want symmetry, set both.

## Performance

Pure PHP. Suitable for typical fuzzy-matching workloads in the range of low thousands of comparisons per second on strings of ~100 characters. If you need C-speed, use the original Python library, or PHP's built-in `levenshtein()` for the unweighted case. Two-row rolling buffer is used for plain Levenshtein; full DP matrices are used for OSA and Damerau-Levenshtein because both need O(m × n) state.

## Contributing

Contributions welcome. Open an issue or PR. The full QA suite is `composer qa` (PHPUnit + PHPStan level max + PHP-CS-Fixer dry-run). All three must be green for a PR to land.

## License

MIT. See [LICENSE](LICENSE). PHP port copyright © 2026 Zach Mowrey. Original Python library copyright © 2016 InfoScout, distributed under the MIT License.
