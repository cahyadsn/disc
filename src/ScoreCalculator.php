<?php

namespace Disc;

class ScoreCalculator
{
    /**
     * Calculate DISC scores from most/least arrays
     */
    public static function calculateScores(array $most, array $least): array
    {
        // Bolt optimization: Explicit if/elseif chain using strict identity checks (===)
        // avoids dynamic array key lookups, isset(), and is_scalar() runtime overhead.
        $result = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

        foreach ($most as $v) {
            if ($v === 'D') $result['D']++;
            else if ($v === 'I') $result['I']++;
            else if ($v === 'S') $result['S']++;
            else if ($v === 'C') $result['C']++;
        }

        foreach ($least as $v) {
            if ($v === 'D') $result['D']--;
            else if ($v === 'I') $result['I']--;
            else if ($v === 'S') $result['S']--;
            else if ($v === 'C') $result['C']--;
        }

        return $result;
    }
}
