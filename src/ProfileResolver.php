<?php
declare(strict_types=1);
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : ProfileResolver.php
DESC         : resolves disc raw scores, line graphs, and personality patterns
AUTHOR       : CAHYA DSN
CREATED DATE : 2026-11-06
UPDATED DATE : 2026-11-06 10:28:00
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

copyright (c) 2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
*/

namespace Disc;

use Throwable;
use mysqli;

final class ProfileResolver
{
    public const int DEFAULT_VAL_D = 15;
    public const int DEFAULT_VAL_I = 14;
    public const int DEFAULT_VAL_S = 15;
    public const int DEFAULT_VAL_C = 14;

    /**
     * Compute raw counts for Most, Least, and Change (Most - Least)
     *
     * @param array<int|string, mixed> $most
     * @param array<int|string, mixed> $least
     * @return array{
     *   most: array{D: int, I: int, S: int, C: int},
     *   least: array{D: int, I: int, S: int, C: int},
     *   change: array{D: int, I: int, S: int, C: int}
     * }
     */
    public static function calculateRawScores(array $most, array $least): array
    {
        $dims = ScoreCalculator::DIMENSIONS;
        $rawMost = array_fill_keys($dims, 0);
        $rawLeast = array_fill_keys($dims, 0);

        foreach ($most as $v) {
            if (is_scalar($v) && isset($rawMost[$v])) {
                $rawMost[$v]++;
            }
        }
        foreach ($least as $v) {
            if (is_scalar($v) && isset($rawLeast[$v])) {
                $rawLeast[$v]++;
            }
        }

        $change = ScoreCalculator::calculateScores($most, $least);

        return [
            'most' => $rawMost,
            'least' => $rawLeast,
            'change' => $change,
        ];
    }

    /**
     * Resolve DISC profile from change scores using DB with fallbacks and caching
     *
     * @param array{D: int, I: int, S: int, C: int} $changeScores
     * @param mysqli|null $db
     * @param string|null $cacheDir
     * @return array{
     *   segment: array{d: int, i: int, s: int, c: int},
     *   pattern: array<string, mixed>
     * }
     */
    public static function resolveProfile(
        array $changeScores,
        ?mysqli $db = null,
        ?string $cacheDir = null
    ): array {
        $cacheKey = "{$changeScores['D']}_{$changeScores['I']}_{$changeScores['S']}_{$changeScores['C']}";
        $cacheFile = $cacheDir !== null ? $cacheDir . '/result_' . $cacheKey . '.json' : null;

        if ($cacheFile !== null && is_readable($cacheFile)) {
            $json = file_get_contents($cacheFile);
            if ($json !== false && json_validate($json)) {
                /** @var mixed $cached */
                $cached = json_decode($json, true);
                if (is_array($cached) && isset($cached['segment'], $cached['pattern'])) {
                    /** @var array{segment: array{d: int, i: int, s: int, c: int}, pattern: array<string, mixed>} $cached */
                    return $cached;
                }
            }
        }

        $data = null;
        if ($db !== null) {
            $sql = "
                SELECT a.*, c.*, m.priority
                FROM (
                    SELECT 1 as priority,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='D' AND value=? LIMIT 1) as d,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='I' AND value=? LIMIT 1) as i,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='S' AND value=? LIMIT 1) as s,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='C' AND value=? LIMIT 1) as c
                    UNION ALL
                    SELECT 2 as priority,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='D' AND value=? LIMIT 1) as d,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='I' AND value=? LIMIT 1) as i,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='S' AND value=? LIMIT 1) as s,
                        (SELECT segment FROM results WHERE graph=3 AND dimension='C' AND value=? LIMIT 1) as c
                ) m
                JOIN pattern_map a ON a.d = m.d AND a.i = m.i AND a.s = m.s AND a.c = m.c
                JOIN patterns c ON c.id=a.pattern
                ORDER BY m.priority ASC
                LIMIT 1";

            try {
                $stmt = $db->prepare($sql);
                if ($stmt) {
                    $valD = $changeScores['D'];
                    $valI = $changeScores['I'];
                    $valS = $changeScores['S'];
                    $valC = $changeScores['C'];
                    $defD = self::DEFAULT_VAL_D;
                    $defI = self::DEFAULT_VAL_I;
                    $defS = self::DEFAULT_VAL_S;
                    $defC = self::DEFAULT_VAL_C;
                    $stmt->bind_param("iiiiiiii", $valD, $valI, $valS, $valC, $defD, $defI, $defS, $defC);
                    $stmt->execute();
                    $dbResult = $stmt->get_result();
                    $row = $dbResult ? $dbResult->fetch_assoc() : null;
                    if ($row) {
                        $data = $row;
                    }
                }
            } catch (Throwable) {
                $data = null;
            }
        }

        if ($data !== null) {
            $resolved = [
                'segment' => [
                    'd' => (int)$data['d'],
                    'i' => (int)$data['i'],
                    's' => (int)$data['s'],
                    'c' => (int)$data['c'],
                ],
                'pattern' => [
                    'id' => (int)($data['id'] ?? 0),
                    'name' => (string)($data['name'] ?? ''),
                    'emotions' => (string)($data['emotions'] ?? ''),
                    'goal' => (string)($data['goal'] ?? ''),
                    'judges_others' => (string)($data['judges_others'] ?? ''),
                    'influences_others' => (string)($data['influences_others'] ?? ''),
                    'organization_value' => (string)($data['organization_value'] ?? ''),
                    'overuses' => (string)($data['overuses'] ?? ''),
                    'under_pressure' => (string)($data['under_pressure'] ?? ''),
                    'fear' => (string)($data['fear'] ?? ''),
                    'effectiveness' => (string)($data['effectiveness'] ?? ''),
                    'description' => (string)($data['description'] ?? ''),
                ],
            ];
        } else {
            $resolved = [
                'segment' => [
                    'd' => self::DEFAULT_VAL_D,
                    'i' => self::DEFAULT_VAL_I,
                    's' => self::DEFAULT_VAL_S,
                    'c' => self::DEFAULT_VAL_C,
                ],
                'pattern' => [
                    'id' => 0,
                    'name' => 'Default / Fallback Profile',
                    'emotions' => 'Balanced',
                    'goal' => 'Stability and effectiveness',
                    'judges_others' => 'Practical standards',
                    'influences_others' => 'Consistency',
                    'organization_value' => 'Reliable execution',
                    'overuses' => 'Caution',
                    'under_pressure' => 'Deliberate action',
                    'fear' => 'Instability',
                    'effectiveness' => 'Adaptability',
                    'description' => 'Profile derived from default fallback dimensions.',
                ],
            ];
        }

        if ($cacheFile !== null) {
            $encoded = json_encode($resolved);
            if ($encoded !== false) {
                @file_put_contents($cacheFile, $encoded, LOCK_EX);
            }
        }

        return $resolved;
    }

    /**
     * Resolve plotting graph segments for Line 1 (Most), Line 2 (Least), Line 3 (Change)
     *
     * @param array{
     *   most: array{D: int, I: int, S: int, C: int},
     *   least: array{D: int, I: int, S: int, C: int},
     *   change: array{D: int, I: int, S: int, C: int}
     * } $scores
     * @param mysqli|null $db
     * @return array{
     *   line1_most: array{D: int, I: int, S: int, C: int},
     *   line2_least: array{D: int, I: int, S: int, C: int},
     *   line3_change: array{D: int, I: int, S: int, C: int}
     * }
     */
    public static function resolveGraphPoints(array $scores, ?mysqli $db = null): array
    {
        $dims = ScoreCalculator::DIMENSIONS;
        $graphs = [
            'line1_most' => array_fill_keys($dims, 0),
            'line2_least' => array_fill_keys($dims, 0),
            'line3_change' => array_fill_keys($dims, 0),
        ];

        if ($db === null) {
            // Default graph coordinates based on raw score distribution
            foreach ($dims as $d) {
                $graphs['line1_most'][$d] = $scores['most'][$d];
                $graphs['line2_least'][$d] = $scores['least'][$d];
                $graphs['line3_change'][$d] = $scores['change'][$d];
            }
            return $graphs;
        }

        $query = 'SELECT segment FROM results WHERE graph=? AND dimension=? AND value=? LIMIT 1';
        try {
            $stmt = $db->prepare($query);
            if ($stmt) {
                $mappings = [
                    1 => ['key' => 'line1_most', 'data' => $scores['most']],
                    2 => ['key' => 'line2_least', 'data' => $scores['least']],
                    3 => ['key' => 'line3_change', 'data' => $scores['change']],
                ];

                foreach ($mappings as $graphNum => $map) {
                    $k = $map['key'];
                    foreach ($dims as $dim) {
                        $val = $map['data'][$dim];
                        $stmt->bind_param("isi", $graphNum, $dim, $val);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $row = $res ? $res->fetch_row() : null;
                        $graphs[$k][$dim] = $row ? (int)$row[0] : $val;
                    }
                }
            }
        } catch (Throwable) {
            // Graceful fallback to raw points on DB error
            foreach ($dims as $d) {
                $graphs['line1_most'][$d] = $scores['most'][$d];
                $graphs['line2_least'][$d] = $scores['least'][$d];
                $graphs['line3_change'][$d] = $scores['change'][$d];
            }
        }

        return $graphs;
    }

    /**
     * Evaluate complete DISC assessment
     *
     * @param array<int|string, mixed> $most
     * @param array<int|string, mixed> $least
     * @param mysqli|null $db
     * @param string|null $cacheDir
     * @return array<string, mixed>
     */
    public static function evaluate(
        array $most,
        array $least,
        ?mysqli $db = null,
        ?string $cacheDir = null
    ): array {
        $scores = self::calculateRawScores($most, $least);
        $profile = self::resolveProfile($scores['change'], $db, $cacheDir);
        $graph = self::resolveGraphPoints($scores, $db);

        return [
            'status' => 'success',
            'scores' => $scores,
            'graph' => $graph,
            'profile' => $profile['pattern'],
            'segment' => $profile['segment'],
        ];
    }
}
