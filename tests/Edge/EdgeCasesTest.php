<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Tests\Edge;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zbmowrey\WeightedLevenshtein\CharCostMap;
use Zbmowrey\WeightedLevenshtein\CharPairCostMap;
use Zbmowrey\WeightedLevenshtein\Distance;

#[CoversNothing]
final class EdgeCasesTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, float}>
     */
    public static function bothDirectionsAgreeProvider(): iterable
    {
        yield 'identical' => ['hello', 'hello', 0.0];
        yield 'one char' => ['a', 'a', 0.0];
        yield 'leading diff' => ['xabc', 'yabc', 1.0];
        yield 'trailing diff' => ['abcx', 'abcy', 1.0];
        yield 'repeated chars shrink' => ['aaaa', 'a', 3.0];
        yield 'repeated chars grow' => ['a', 'aaaa', 3.0];
        yield 'full substitution' => ['abcd', 'wxyz', 4.0];
    }

    #[Test]
    #[DataProvider('bothDirectionsAgreeProvider')]
    public function unweightedAlgorithmsAgreeOnSimpleCases(string $a, string $b, float $expected): void
    {
        self::assertSame($expected, Distance::levenshtein($a, $b));
        self::assertSame($expected, Distance::optimalStringAlignment($a, $b));
        self::assertSame($expected, Distance::damerauLevenshtein($a, $b));
    }

    #[Test]
    public function allEmpty(): void
    {
        self::assertSame(0.0, Distance::levenshtein('', ''));
        self::assertSame(0.0, Distance::optimalStringAlignment('', ''));
        self::assertSame(0.0, Distance::damerauLevenshtein('', ''));
    }

    #[Test]
    public function osaForbidsOverlappingTransposes(): void
    {
        // 'abc' -> 'cba' is two non-adjacent transpositions; OSA can't do it as
        // two transposes (overlapping substrings), so it falls back to 2 subs.
        self::assertSame(2.0, Distance::optimalStringAlignment('abc', 'cba'));
        // True Damerau-Levenshtein can do it differently but still needs 2 ops.
        self::assertSame(2.0, Distance::damerauLevenshtein('abc', 'cba'));
    }

    #[Test]
    public function osaTransposeNoOverlap(): void
    {
        // 'ca' -> 'ac' is a single adjacent transposition: cost 1.
        self::assertSame(1.0, Distance::optimalStringAlignment('ca', 'ac'));
    }

    #[Test]
    public function damerauPrefersTransposeWhenCheaper(): void
    {
        $tw = CharPairCostMap::uniform()->withCost('a', 'b', 0.25);
        // Single adjacent swap of a,b in 'ab' -> 'ba' costs 0.25 with our map.
        self::assertSame(0.25, Distance::damerauLevenshtein('ab', 'ba', null, null, null, $tw));
    }

    #[Test]
    public function nonAsciiRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Distance::levenshtein("caf\xC3\xA9", 'cafe');
    }

    #[Test]
    public function nonAsciiRejectedInB(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Distance::optimalStringAlignment('cafe', "caf\xC3\xA9");
    }

    #[Test]
    public function charCostMapRejectsMultibyte(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CharCostMap::uniform()->withCost("\xC3\xA9", 1.0);
    }

    #[Test]
    public function charCostMapRejectsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CharCostMap::uniform()->withCost('', 1.0);
    }

    #[Test]
    public function charPairCostMapRejectsMultibyte(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CharPairCostMap::uniform()->withCost("\xC3\xA9", 'a', 1.0);
    }

    #[Test]
    public function immutability(): void
    {
        $a = CharCostMap::uniform();
        $b = $a->withCost('a', 5.0);
        self::assertSame(1.0, $a->cost('a'));
        self::assertSame(5.0, $b->cost('a'));

        $p = CharPairCostMap::uniform();
        $q = $p->withCost('a', 'b', 2.0);
        self::assertSame(1.0, $p->cost('a', 'b'));
        self::assertSame(2.0, $q->cost('a', 'b'));
    }

    #[Test]
    public function caseSensitive(): void
    {
        self::assertSame(1.0, Distance::levenshtein('a', 'A'));
        self::assertSame(4.0, Distance::levenshtein('ABCD', 'abcd'));
    }

    #[Test]
    public function asymmetricSubstitution(): void
    {
        $sw = CharPairCostMap::uniform()->withCost('a', 'b', 0.25);
        self::assertSame(0.25, Distance::levenshtein('a', 'b', null, null, $sw));
        // Reverse direction still uses default 1.0.
        self::assertSame(1.0, Distance::levenshtein('b', 'a', null, null, $sw));
    }

    #[Test]
    public function damerauOnlyTransposes(): void
    {
        // 'abcdef' -> 'badcfe' is three non-overlapping adjacent swaps.
        self::assertSame(3.0, Distance::damerauLevenshtein('abcdef', 'badcfe'));
        self::assertSame(3.0, Distance::optimalStringAlignment('abcdef', 'badcfe'));
    }

    #[Test]
    public function freeFunctionAliases(): void
    {
        self::assertSame(
            Distance::levenshtein('abc', 'abd'),
            \Zbmowrey\WeightedLevenshtein\lev('abc', 'abd'),
        );
        self::assertSame(
            Distance::optimalStringAlignment('ab', 'ba'),
            \Zbmowrey\WeightedLevenshtein\osa('ab', 'ba'),
        );
        self::assertSame(
            Distance::damerauLevenshtein('ab', 'ba'),
            \Zbmowrey\WeightedLevenshtein\dam_lev('ab', 'ba'),
        );
    }
}
