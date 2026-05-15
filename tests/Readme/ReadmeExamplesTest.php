<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Tests\Readme;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zbmowrey\WeightedLevenshtein\CharCostMap;
use Zbmowrey\WeightedLevenshtein\CharPairCostMap;
use Zbmowrey\WeightedLevenshtein\Distance;

use function Zbmowrey\WeightedLevenshtein\dam_lev;
use function Zbmowrey\WeightedLevenshtein\lev;
use function Zbmowrey\WeightedLevenshtein\osa;

/**
 * Every README example has a corresponding assertion here. If you change the
 * README, change this test. If you change this test, change the README.
 */
#[CoversNothing]
final class ReadmeExamplesTest extends TestCase
{
    #[Test]
    public function quickStart(): void
    {
        self::assertSame(3.0, Distance::levenshtein('kitten', 'sitting'));
        self::assertSame(1.0, Distance::optimalStringAlignment('ca', 'ac'));
        self::assertSame(2.0, Distance::damerauLevenshtein('ab', 'bca'));
    }

    #[Test]
    public function quickStartInsertExample(): void
    {
        $insertCosts = CharCostMap::uniform()->withCost('D', 1.5);
        self::assertSame(1.5, Distance::levenshtein('BANANAS', 'BANDANAS', $insertCosts));
    }

    #[Test]
    public function fullExampleDeletion(): void
    {
        $insertCosts = CharCostMap::uniform()->withCost('D', 1.5);
        $deleteCosts = CharCostMap::uniform()->withCost('S', 0.5);
        self::assertSame(0.5, Distance::levenshtein('BANANAS', 'BANANA', $insertCosts, $deleteCosts));
    }

    #[Test]
    public function fullExampleAsymmetricSubstitution(): void
    {
        $subs = CharPairCostMap::uniform()->withCost('H', 'B', 1.25);
        self::assertSame(1.25, Distance::levenshtein('HANANA', 'BANANA', null, null, $subs));
        self::assertSame(1.0, Distance::levenshtein('BANANA', 'HANANA', null, null, $subs));

        $subs = $subs->withCost('B', 'H', 1.25);
        self::assertSame(1.25, Distance::levenshtein('BANANA', 'HANANA', null, null, $subs));
    }

    #[Test]
    public function fullExampleAsymmetricTransposition(): void
    {
        $transposes = CharPairCostMap::uniform()->withCost('A', 'B', 0.75);
        self::assertSame(
            0.75,
            Distance::damerauLevenshtein('ABNANA', 'BANANA', null, null, null, $transposes),
        );
        self::assertSame(
            1.0,
            Distance::damerauLevenshtein('BANANA', 'ABNANA', null, null, null, $transposes),
        );

        $transposes = $transposes->withCost('B', 'A', 0.75);
        self::assertSame(
            0.75,
            Distance::damerauLevenshtein('BANANA', 'ABNANA', null, null, null, $transposes),
        );
    }

    #[Test]
    public function freeFunctionAliases(): void
    {
        self::assertSame(3.0, lev('kitten', 'sitting'));
        self::assertSame(1.0, osa('ca', 'ac'));
        self::assertSame(1.0, dam_lev('ab', 'ba'));
    }
}
