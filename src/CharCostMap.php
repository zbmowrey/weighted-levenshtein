<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein;

use InvalidArgumentException;

/**
 * Immutable per-character cost lookup for one-argument edit operations
 * (insertion and deletion).
 *
 * Keys are restricted to single-byte ASCII characters (code points 0–127),
 * matching the constraint of the original Python library.
 */
final readonly class CharCostMap
{
    private const int ALPHABET_SIZE = 128;

    /**
     * Costs indexed by ASCII code point (0–127). Any code point not present
     * uses {@see $defaultCost}.
     *
     * @var array<int, float>
     */
    private array $costs;

    /**
     * @param array<int, float> $costs
     */
    private function __construct(
        public float $defaultCost,
        array $costs,
    ) {
        $this->costs = $costs;
    }

    /**
     * Construct a map where every character costs $defaultCost.
     */
    public static function uniform(float $defaultCost = 1.0): self
    {
        return new self($defaultCost, []);
    }

    /**
     * Return a new map with $cost as the cost for $char.
     *
     * @throws InvalidArgumentException if $char is not exactly one ASCII byte.
     */
    public function withCost(string $char, float $cost): self
    {
        $code = self::assertSingleAsciiByte($char);
        $costs = $this->costs;
        $costs[$code] = $cost;

        return new self($this->defaultCost, $costs);
    }

    /**
     * Return the cost for $char, falling back to the default.
     *
     * @throws InvalidArgumentException if $char is not exactly one ASCII byte.
     */
    public function cost(string $char): float
    {
        $code = self::assertSingleAsciiByte($char);

        return $this->costs[$code] ?? $this->defaultCost;
    }

    /**
     * Materialize the map as a length-128 array of floats, indexed by ASCII code.
     * Used internally by the algorithm implementations for O(1) lookup.
     *
     * @return list<float>
     */
    public function toArray(): array
    {
        $out = array_fill(0, self::ALPHABET_SIZE, $this->defaultCost);
        foreach ($this->costs as $code => $cost) {
            $out[$code] = $cost;
        }

        return array_values($out);
    }

    private static function assertSingleAsciiByte(string $char): int
    {
        if (strlen($char) !== 1) {
            throw new InvalidArgumentException(
                'Character must be a single byte; got string of length ' . strlen($char) . '.',
            );
        }
        $code = ord($char);
        if ($code > 127) {
            throw new InvalidArgumentException(
                'Character must be ASCII (code point 0–127); got code point ' . $code . '.',
            );
        }

        return $code;
    }
}
