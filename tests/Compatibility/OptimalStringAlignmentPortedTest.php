<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Tests\Compatibility;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zbmowrey\WeightedLevenshtein\CharCostMap;
use Zbmowrey\WeightedLevenshtein\CharPairCostMap;
use Zbmowrey\WeightedLevenshtein\Distance;
use Zbmowrey\WeightedLevenshtein\OptimalStringAlignment;

/**
 * Ports test_osa* and TestClevUsingDefaultValues::test_osa from the reference
 * Python suite (test/test.py).
 */
#[CoversClass(Distance::class)]
#[CoversClass(OptimalStringAlignment::class)]
final class OptimalStringAlignmentPortedTest extends TestCase
{
    #[Test]
    public function testOsa(): void
    {
        $this->assertSame(0.0, Distance::optimalStringAlignment('1234', '1234'));
        $this->assertSame(4.0, Distance::optimalStringAlignment('', '1234'));
        $this->assertSame(4.0, Distance::optimalStringAlignment('1234', ''));
        $this->assertSame(0.0, Distance::optimalStringAlignment('', ''));
        $this->assertSame(2.0, Distance::optimalStringAlignment('1234', '12'));
        $this->assertSame(2.0, Distance::optimalStringAlignment('1234', '14'));
        $this->assertSame(3.0, Distance::optimalStringAlignment('1111', '1'));
    }

    #[Test]
    public function testOsaInsert(): void
    {
        $iw = CharCostMap::uniform()->withCost('a', 5);
        $this->assertSame(5.0, Distance::optimalStringAlignment('', 'a', $iw));
        $this->assertSame(1.0, Distance::optimalStringAlignment('a', '', $iw));
        $this->assertSame(10.0, Distance::optimalStringAlignment('', 'aa', $iw));
        $this->assertSame(5.0, Distance::optimalStringAlignment('a', 'aa', $iw));
        $this->assertSame(1.0, Distance::optimalStringAlignment('aa', 'a', $iw));
        $this->assertSame(0.0, Distance::optimalStringAlignment('asdf', 'asdf', $iw));
        $this->assertSame(3.0, Distance::optimalStringAlignment('xyz', 'abc', $iw));
        $this->assertSame(5.0, Distance::optimalStringAlignment('xyz', 'axyz', $iw));
        $this->assertSame(5.0, Distance::optimalStringAlignment('x', 'ax', $iw));
    }

    #[Test]
    public function testOsaDelete(): void
    {
        $dw = CharCostMap::uniform()->withCost('z', 7.5);
        $this->assertSame(1.0, Distance::optimalStringAlignment('', 'z', null, $dw));
        $this->assertSame(7.5, Distance::optimalStringAlignment('z', '', null, $dw));
        $this->assertSame(3.0, Distance::optimalStringAlignment('xyz', 'zzxz', null, $dw));
        $this->assertSame(18.0, Distance::optimalStringAlignment('zzxzzz', 'xyz', null, $dw));
    }

    #[Test]
    public function testOsaSubstitute(): void
    {
        $sw = CharPairCostMap::uniform()
            ->withCost('a', 'z', 1.2)
            ->withCost('z', 'a', 0.1);

        $this->assertEqualsWithDelta(1.2, Distance::optimalStringAlignment('a', 'z', null, null, $sw), 1e-12);
        $this->assertEqualsWithDelta(0.1, Distance::optimalStringAlignment('z', 'a', null, null, $sw), 1e-12);
        $this->assertSame(1.0, Distance::optimalStringAlignment('a', '', null, null, $sw));
        $this->assertSame(1.0, Distance::optimalStringAlignment('', 'a', null, null, $sw));
        $this->assertEqualsWithDelta(4.2, Distance::optimalStringAlignment('asdf', 'zzzz', null, null, $sw), 1e-12);
        $this->assertSame(4.0, Distance::optimalStringAlignment('asdf', 'zz', null, null, $sw));
        $this->assertEqualsWithDelta(1.2, Distance::optimalStringAlignment('asdf', 'zsdf', null, null, $sw), 1e-12);
        $this->assertEqualsWithDelta(0.1, Distance::optimalStringAlignment('zsdf', 'asdf', null, null, $sw), 1e-12);
    }

    #[Test]
    public function testOsaTranspose(): void
    {
        $tw = CharPairCostMap::uniform()
            ->withCost('a', 'z', 1.5)
            ->withCost('z', 'a', 0.5);

        $this->assertSame(1.5, Distance::optimalStringAlignment('az', 'za', null, null, null, $tw));
        $this->assertSame(0.5, Distance::optimalStringAlignment('za', 'az', null, null, null, $tw));
        $this->assertSame(3.0, Distance::optimalStringAlignment('az', 'zfa', null, null, null, $tw));
        $this->assertSame(2.0, Distance::optimalStringAlignment('azza', 'zaaz', null, null, null, $tw));
        $this->assertSame(2.0, Distance::optimalStringAlignment('zaaz', 'azza', null, null, null, $tw));
        $this->assertSame(2.0, Distance::optimalStringAlignment('azbza', 'zabaz', null, null, null, $tw));
        $this->assertSame(2.0, Distance::optimalStringAlignment('zabaz', 'azbza', null, null, null, $tw));
        $this->assertSame(3.0, Distance::optimalStringAlignment('azxza', 'zayaz', null, null, null, $tw));
        $this->assertSame(3.0, Distance::optimalStringAlignment('zaxaz', 'azyza', null, null, null, $tw));
    }
}
