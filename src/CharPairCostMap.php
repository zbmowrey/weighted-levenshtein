<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein;

use InvalidArgumentException;

/**
 * Immutable per-ordered-pair cost lookup for two-argument edit operations
 * (substitution and transposition).
 *
 * Costs are asymmetric: the cost of (from='A', to='B') is independent of
 * (from='B', to='A'). Both keys must be single-byte ASCII characters
 * (code points 0–127).
 */
final readonly class CharPairCostMap
{
    private const int ALPHABET_SIZE = 128;

    /**
     * Costs indexed by (from << 7) | to. Any pair not present uses {@see $defaultCost}.
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
     * Construct a map where every (from, to) pair costs $defaultCost.
     */
    public static function uniform(float $defaultCost = 1.0): self
    {
        return new self($defaultCost, []);
    }

    /**
     * Return a new map with $cost as the cost for the ordered pair ($from, $to).
     *
     * @throws InvalidArgumentException if either $from or $to is not exactly one ASCII byte.
     */
    public function withCost(string $from, string $to, float $cost): self
    {
        $fromCode = self::assertSingleAsciiByte($from, 'from');
        $toCode = self::assertSingleAsciiByte($to, 'to');
        $costs = $this->costs;
        $costs[($fromCode << 7) | $toCode] = $cost;

        return new self($this->defaultCost, $costs);
    }

    /**
     * Return the cost for the ordered pair ($from, $to), falling back to the default.
     *
     * @throws InvalidArgumentException if either $from or $to is not exactly one ASCII byte.
     */
    public function cost(string $from, string $to): float
    {
        $fromCode = self::assertSingleAsciiByte($from, 'from');
        $toCode = self::assertSingleAsciiByte($to, 'to');

        return $this->costs[($fromCode << 7) | $toCode] ?? $this->defaultCost;
    }

    /**
     * Materialize the map as a flat length-(128*128) array of floats, indexed
     * by (fromCode * 128) + toCode. Used internally by the algorithm
     * implementations for O(1) lookup.
     *
     * @return list<float>
     */
    public function toFlatArray(): array
    {
        $size = self::ALPHABET_SIZE * self::ALPHABET_SIZE;
        $out = array_fill(0, $size, $this->defaultCost);
        foreach ($this->costs as $packed => $cost) {
            $from = $packed >> 7;
            $to = $packed & 0x7F;
            $out[$from * self::ALPHABET_SIZE + $to] = $cost;
        }

        return array_values($out);
    }

    private static function assertSingleAsciiByte(string $char, string $name): int
    {
        if (strlen($char) !== 1) {
            throw new InvalidArgumentException(
                $name . ' must be a single byte; got string of length ' . strlen($char) . '.',
            );
        }
        $code = ord($char);
        if ($code > 127) {
            throw new InvalidArgumentException(
                $name . ' must be ASCII (code point 0–127); got code point ' . $code . '.',
            );
        }

        return $code;
    }
}
