# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] — 2026-05-15

### Changed
- Expanded `OcrConfusions::common()` to cover a more comprehensive set of high-confidence OCR confusions. New pairs (all bidirectional): `0`↔`D`, `O`↔`D`, `O`↔`Q`, `1`↔`7`, `2`↔`7`, `7`↔`T`, `7`↔`Z`, `7`↔`z`, `3`↔`5`, `3`↔`8`, `f`↔`t`, `r`↔`n`, `C`↔`G`, `E`↔`F`, `M`↔`N`, `P`↔`R`, `U`↔`V`, `V`↔`Y`. The cost parameter and method signature are unchanged.

## [1.1.0] — 2026-05-15

### Added
- `Zbmowrey\WeightedLevenshtein\Presets\OcrConfusions` — curated substitution-cost preset for common OCR confusions (e.g. `0`↔`O`, `1`↔`l`, `5`↔`S`). Cost is a single configurable parameter.
- `Zbmowrey\WeightedLevenshtein\Presets\QwertyKeyboard` — substitution and transposition cost presets derived from the physical US QWERTY layout. Adjacent and near-adjacent keys are weighted below the default 1.0.
- README section documenting preset usage and showing how to layer custom overrides on top of a preset.

## [1.0.0] — 2026-05-15

Initial release. Feature parity with weighted-levenshtein 0.2.2.

### Added
- `Zbmowrey\WeightedLevenshtein\Distance` facade with `levenshtein()`, `optimalStringAlignment()`, and `damerauLevenshtein()` methods.
- `CharCostMap` immutable value object for per-character insert/delete costs.
- `CharPairCostMap` immutable value object for per-ordered-pair substitute/transpose costs.
- Free functions `lev()`, `osa()`, `dam_lev()` as short aliases.
- Full PHPUnit test suite, including ported reference tests, edge cases, README example tests, and optional cross-validation against the Python reference.
- CI on GitHub Actions for PHP 8.4.
