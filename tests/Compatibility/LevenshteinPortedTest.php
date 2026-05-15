<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Tests\Compatibility;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zbmowrey\WeightedLevenshtein\CharCostMap;
use Zbmowrey\WeightedLevenshtein\CharPairCostMap;
use Zbmowrey\WeightedLevenshtein\Distance;
use Zbmowrey\WeightedLevenshtein\Levenshtein;

/**
 * Ports test_lev*, TestClevUsingDefaultValues::test_lev from the reference
 * Python suite (test/test.py).
 */
#[CoversClass(Distance::class)]
#[CoversClass(Levenshtein::class)]
#[CoversClass(CharCostMap::class)]
#[CoversClass(CharPairCostMap::class)]
final class LevenshteinPortedTest extends TestCase
{
    #[Test]
    public function testLev(): void
    {
        $this->assertSame(0.0, Distance::levenshtein('1234', '1234'));
        $this->assertSame(4.0, Distance::levenshtein('', '1234'));
        $this->assertSame(4.0, Distance::levenshtein('1234', ''));
        $this->assertSame(0.0, Distance::levenshtein('', ''));
        $this->assertSame(2.0, Distance::levenshtein('1234', '12'));
        $this->assertSame(2.0, Distance::levenshtein('1234', '14'));
        $this->assertSame(3.0, Distance::levenshtein('1111', '1'));
    }

    #[Test]
    public function testLevInsert(): void
    {
        $iw = CharCostMap::uniform()->withCost('a', 5);
        $this->assertSame(5.0, Distance::levenshtein('', 'a', $iw));
        $this->assertSame(1.0, Distance::levenshtein('a', '', $iw));
        $this->assertSame(10.0, Distance::levenshtein('', 'aa', $iw));
        $this->assertSame(5.0, Distance::levenshtein('a', 'aa', $iw));
        $this->assertSame(1.0, Distance::levenshtein('aa', 'a', $iw));
        $this->assertSame(0.0, Distance::levenshtein('asdf', 'asdf', $iw));
        $this->assertSame(3.0, Distance::levenshtein('xyz', 'abc', $iw));
        $this->assertSame(5.0, Distance::levenshtein('xyz', 'axyz', $iw));
        $this->assertSame(5.0, Distance::levenshtein('x', 'ax', $iw));
    }

    #[Test]
    public function testLevDelete(): void
    {
        $dw = CharCostMap::uniform()->withCost('z', 7.5);
        $this->assertSame(1.0, Distance::levenshtein('', 'z', null, $dw));
        $this->assertSame(7.5, Distance::levenshtein('z', '', null, $dw));
        $this->assertSame(3.0, Distance::levenshtein('xyz', 'zzxz', null, $dw));
        $this->assertSame(18.0, Distance::levenshtein('zzxzzz', 'xyz', null, $dw));
    }

    #[Test]
    public function testLevSubstitute(): void
    {
        $sw = CharPairCostMap::uniform()
            ->withCost('a', 'z', 1.2)
            ->withCost('z', 'a', 0.1);

        $this->assertEqualsWithDelta(1.2, Distance::levenshtein('a', 'z', null, null, $sw), 1e-12);
        $this->assertEqualsWithDelta(0.1, Distance::levenshtein('z', 'a', null, null, $sw), 1e-12);
        $this->assertSame(1.0, Distance::levenshtein('a', '', null, null, $sw));
        $this->assertSame(1.0, Distance::levenshtein('', 'a', null, null, $sw));
        $this->assertEqualsWithDelta(4.2, Distance::levenshtein('asdf', 'zzzz', null, null, $sw), 1e-12);
        $this->assertSame(4.0, Distance::levenshtein('asdf', 'zz', null, null, $sw));
        $this->assertEqualsWithDelta(1.2, Distance::levenshtein('asdf', 'zsdf', null, null, $sw), 1e-12);
        $this->assertEqualsWithDelta(0.1, Distance::levenshtein('zsdf', 'asdf', null, null, $sw), 1e-12);
    }
}
