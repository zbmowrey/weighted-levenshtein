<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Tests\Presets;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zbmowrey\WeightedLevenshtein\Distance;
use Zbmowrey\WeightedLevenshtein\Presets\QwertyKeyboard;

#[CoversClass(QwertyKeyboard::class)]
final class QwertyKeyboardTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string, 1: string, 2: float}>
     */
    public static function knownPairsProvider(): iterable
    {
        // Adjacent same-row neighbors → 0.5
        yield 'q-w adjacent' => ['q', 'w', 0.5];
        yield 'w-e adjacent' => ['w', 'e', 0.5];
        yield 'a-s adjacent' => ['a', 's', 0.5];
        yield '1-2 adjacent' => ['1', '2', 0.5];
        // Diagonal adjacency (close-diagonal) → 0.5
        yield 'q-a diagonal' => ['q', 'a', 0.5];
        yield 'e-d diagonal' => ['e', 'd', 0.5];
        // Diagonal-far (not strictly adjacent) → 0.75
        yield 'q-s diagonal-far' => ['q', 's', 0.75];
        // Two keys apart same row → 0.75
        yield 'q-e two apart' => ['q', 'e', 0.75];
        yield 'a-d two apart' => ['a', 'd', 0.75];
        // Far away → default 1.0
        yield 'q-p far apart' => ['q', 'p', 1.0];
        yield 'a-m far apart' => ['a', 'm', 1.0];
    }

    #[Test]
    #[DataProvider('knownPairsProvider')]
    public function substituteCostMatchesAdjacency(string $a, string $b, float $expected): void
    {
        $map = QwertyKeyboard::substituteCosts();
        self::assertSame($expected, Distance::levenshtein($a, $b, null, null, $map));
        // Always symmetric — flipping the pair should give the same cost.
        self::assertSame($expected, Distance::levenshtein($b, $a, null, null, $map));
    }

    #[Test]
    public function uppercaseLettersAreMirrored(): void
    {
        $map = QwertyKeyboard::substituteCosts();
        self::assertSame(0.5, Distance::levenshtein('Q', 'W', null, null, $map));
        self::assertSame(0.5, Distance::levenshtein('A', 'S', null, null, $map));
        self::assertSame(0.5, Distance::levenshtein('E', 'D', null, null, $map));
    }

    #[Test]
    public function mixedCaseIsNotPopulated(): void
    {
        $map = QwertyKeyboard::substituteCosts();
        // 'q' adjacent to 'w', but mixed case retains default 1.0.
        self::assertSame(1.0, Distance::levenshtein('q', 'W', null, null, $map));
        self::assertSame(1.0, Distance::levenshtein('Q', 'w', null, null, $map));
    }

    #[Test]
    public function transposeCostsApplyInDamerauLevenshtein(): void
    {
        $transposeMap = QwertyKeyboard::transposeCosts();
        // 'qw' -> 'wq' is a single adjacent transposition. q and w are same-row
        // neighbors (distance 1.0) so the transpose cost should be the
        // adjacent cost of 0.5.
        self::assertSame(0.5, Distance::damerauLevenshtein('qw', 'wq', null, null, null, $transposeMap));
    }

    #[Test]
    public function customCostParametersAreHonored(): void
    {
        $map = QwertyKeyboard::substituteCosts(adjacentCost: 0.1, nearCost: 0.3);
        self::assertSame(0.1, Distance::levenshtein('q', 'w', null, null, $map));
        self::assertSame(0.3, Distance::levenshtein('q', 'e', null, null, $map));
        // Far pairs still default.
        self::assertSame(1.0, Distance::levenshtein('q', 'p', null, null, $map));
    }
}
