<?php
declare(strict_types=1);
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : ScoreCalculator.php
DESC         : score calculator class for disc apps
AUTHOR       : CAHYA DSN
CREATED DATE : 2026-10-02
UPDATED DATE : 2026-11-06 08:20:00
================================================================================
MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
================================================================================
*/

namespace Disc;

final class ScoreCalculator
{
    /**
     * DISC Dimensions.
     */
    public const array DIMENSIONS = ['D', 'I', 'S', 'C'];

    /**
     * Calculate DISC scores from most/least arrays
     *
     * @param array<int|string, mixed> $most
     * @param array<int|string, mixed> $least
     * @return array{D: int, I: int, S: int, C: int}
     */
    public static function calculateScores(array $most, array $least): array
    {
        // Bolt optimization: Explicit if/elseif chain using strict identity checks (===)
        // avoids dynamic array key lookups, isset(), and is_scalar() runtime overhead.
        $result = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

        foreach ($most as $v) {
            if ($v === 'D') {
                $result['D']++;
            } elseif ($v === 'I') {
                $result['I']++;
            } elseif ($v === 'S') {
                $result['S']++;
            } elseif ($v === 'C') {
                $result['C']++;
            }
        }

        foreach ($least as $v) {
            if ($v === 'D') {
                $result['D']--;
            } elseif ($v === 'I') {
                $result['I']--;
            } elseif ($v === 'S') {
                $result['S']--;
            } elseif ($v === 'C') {
                $result['C']--;
            }
        }

        return $result;
    }
}
