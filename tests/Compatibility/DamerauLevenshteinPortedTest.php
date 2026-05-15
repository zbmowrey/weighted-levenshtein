<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Tests\Compatibility;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zbmowrey\WeightedLevenshtein\CharCostMap;
use Zbmowrey\WeightedLevenshtein\CharPairCostMap;
use Zbmowrey\WeightedLevenshtein\DamerauLevenshtein;
use Zbmowrey\WeightedLevenshtein\Distance;

/**
 * Ports test_dl* and TestClevUsingDefaultValues::test_dl from the reference
 * Python suite (test/test.py).
 */
#[CoversClass(Distance::class)]
#[CoversClass(DamerauLevenshtein::class)]
final class DamerauLevenshteinPortedTest extends TestCase
{
    #[Test]
    public function testDl(): void
    {
        $this->assertSame(0.0, Distance::damerauLevenshtein('', ''));
        $this->assertSame(1.0, Distance::damerauLevenshtein('', 'a'));
        $this->assertSame(1.0, Distance::damerauLevenshtein('a', ''));
        $this->assertSame(1.0, Distance::damerauLevenshtein('a', 'b'));
        $this->assertSame(1.0, Distance::damerauLevenshtein('a', 'ab'));
        $this->assertSame(1.0, Distance::damerauLevenshtein('ab', 'ba'));
        $this->assertSame(2.0, Distance::damerauLevenshtein('ab', 'bca'));
        $this->assertSame(2.0, Distance::damerauLevenshtein('bca', 'ab'));
        $this->assertSame(3.0, Distance::damerauLevenshtein('ab', 'bdca'));
        $this->assertSame(3.0, Distance::damerauLevenshtein('bdca', 'ab'));
    }

    #[Test]
    public function testDlTransposeInsertCost(): void
    {
        $iw = CharCostMap::uniform()->withCost('c', 1.9);
        $this->assertEqualsWithDelta(2.9, Distance::damerauLevenshtein('ab', 'bca', $iw), 1e-12);
        $this->assertEqualsWithDelta(3.9, Distance::damerauLevenshtein('ab', 'bdca', $iw), 1e-12);
        $this->assertSame(2.0, Distance::damerauLevenshtein('bca', 'ab', $iw));
    }

    #[Test]
    public function testDlTransposeDeleteCost(): void
    {
        $dw = CharCostMap::uniform()->withCost('c', 1.9);
        $this->assertEqualsWithDelta(2.9, Distance::damerauLevenshtein('bca', 'ab', null, $dw), 1e-12);
        $this->assertEqualsWithDelta(3.9, Distance::damerauLevenshtein('bdca', 'ab', null, $dw), 1e-12);
        $this->assertSame(2.0, Distance::damerauLevenshtein('ab', 'bca', null, $dw));
    }

    #[Test]
    public function testDlTransposeAbCost(): void
    {
        $tw = CharPairCostMap::uniform()->withCost('a', 'b', 1.5);
        $this->assertSame(2.5, Distance::damerauLevenshtein('ab', 'bca', null, null, null, $tw));
        $this->assertSame(2.0, Distance::damerauLevenshtein('bca', 'ab', null, null, null, $tw));
    }

    #[Test]
    public function testDlTransposeBaCost(): void
    {
        $tw = CharPairCostMap::uniform()->withCost('b', 'a', 1.5);
        $this->assertSame(2.0, Distance::damerauLevenshtein('ab', 'bca', null, null, null, $tw));
        $this->assertSame(2.5, Distance::damerauLevenshtein('bca', 'ab', null, null, null, $tw));
    }
}
