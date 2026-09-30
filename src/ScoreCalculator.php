<?php

namespace Disc;

class ScoreCalculator
{
    /**
     * Calculate DISC scores from most/least arrays
     */
    public static function calculateScores(array $most, array $least): array
    {
        $result = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

        foreach ($most as $v) {
            if (is_string($v) && isset($result[$v])) {
                $result[$v]++;
            }
        }

        foreach ($least as $v) {
            if (is_string($v) && isset($result[$v])) {
                $result[$v]--;
            }
        }

        return $result;
    }
}
