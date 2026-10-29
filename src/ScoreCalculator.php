<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : ScoreCalculator.php
DESC         : score calculator class for disc apps
AUTHOR       : CAHYA DSN
CREATED DATE : 2026-10-02
UPDATED DATE : 2026-10-20 08:02:19
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

class ScoreCalculator
{
    /**
     * Calculate DISC scores from most/least arrays
     */
    public static function calculateScores(array $most, array $least): array
    {
        $result = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

        $mostCounts = array_count_values(array_filter($most, 'is_scalar'));
        $leastCounts = array_count_values(array_filter($least, 'is_scalar'));

        foreach (['D', 'I', 'S', 'C'] as $dim) {
            $result[$dim] += $mostCounts[$dim] ?? 0;
            $result[$dim] -= $leastCounts[$dim] ?? 0;
        }

        return $result;
    }
}
