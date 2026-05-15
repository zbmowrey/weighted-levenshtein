<?php

declare(strict_types=1);

namespace Zbmowrey\WeightedLevenshtein\Tests\Presets;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zbmowrey\WeightedLevenshtein\Distance;
use Zbmowrey\WeightedLevenshtein\Presets\OcrConfusions;

#[CoversClass(OcrConfusions::class)]
final class OcrConfusionsTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function symmetricPairsProvider(): iterable
    {
        $pairs = [
            ['0', 'O'], ['0', 'o'], ['O', 'o'],
            ['1', 'l'], ['1', 'I'], ['l', 'I'],
            ['5', 'S'], ['8', 'B'], ['2', 'Z'],
            ['6', 'G'], ['9', 'g'], ['9', 'q'],
            ['c', 'e'], ['n', 'h'], ['u', 'v'], ['m', 'n'],
        ];
        foreach ($pairs as [$a, $b]) {
            yield "{$a} <-> {$b}" => [$a, $b];
        }
    }

    /**
     * Every curated confusion pair must produce the same cheap cost in both
     * directions when used with Levenshtein.
     */
    #[Test]
    #[DataProvider('symmetricPairsProvider')]
    public function curatedPairsAreSymmetric(string $a, string $b): void
    {
        $map = OcrConfusions::common(0.25);
        self::assertSame(0.25, Distance::levenshtein($a, $b, null, null, $map));
        self::assertSame(0.25, Distance::levenshtein($b, $a, null, null, $map));
    }

    #[Test]
    public function unrelatedPairsKeepDefaultCost(): void
    {
        $map = OcrConfusions::common(0.25);
        // 'A' and 'Z' are not visually confused and remain at default 1.0.
        self::assertSame(1.0, Distance::levenshtein('A', 'Z', null, null, $map));
        self::assertSame(1.0, Distance::levenshtein('K', 'Q', null, null, $map));
    }

    #[Test]
    public function ocrLikeWordExample(): void
    {
        $map = OcrConfusions::common(0.25);
        // 'FOOD' vs 'F00D' — two uppercase O <-> 0 substitutions at 0.25 each.
        self::assertSame(0.5, Distance::levenshtein('FOOD', 'F00D', null, null, $map));
        // 'Hello' vs 'He11o' — two lowercase l <-> 1 substitutions at 0.25 each.
        self::assertSame(0.5, Distance::levenshtein('Hello', 'He11o', null, null, $map));
    }

    #[Test]
    public function costParameterScales(): void
    {
        $strict = OcrConfusions::common(0.1);
        $lenient = OcrConfusions::common(0.5);
        self::assertSame(0.1, Distance::levenshtein('O', '0', null, null, $strict));
        self::assertSame(0.5, Distance::levenshtein('O', '0', null, null, $lenient));
    }

    #[Test]
    public function userCanCustomizeOnTopOfPreset(): void
    {
        // Layer a domain-specific override: in this user's data, '5'<->'S' is much rarer.
        $map = OcrConfusions::common(0.25)->withCost('5', 'S', 0.05);
        self::assertSame(0.05, Distance::levenshtein('5', 'S', null, null, $map));
        // Reverse direction was not overridden — still 0.25 from the preset.
        self::assertSame(0.25, Distance::levenshtein('S', '5', null, null, $map));
        // Other preset pairs untouched.
        self::assertSame(0.25, Distance::levenshtein('0', 'O', null, null, $map));
    }
}
