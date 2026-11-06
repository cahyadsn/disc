<?php
declare(strict_types=1);

namespace Disc\Tests;

use PHPUnit\Framework\TestCase;
use Disc\ProfileResolver;

class ProfileResolverTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function generateSampleSelections(string $dim, int $count = 28): array
    {
        return array_fill(0, $count, $dim);
    }

    public function testCalculateRawScores(): void
    {
        $m = ['D', 'D', 'I', 'S', 'C', 'D'];
        $l = ['I', 'S', 'S', 'C', 'C', '#'];

        $scores = ProfileResolver::calculateRawScores($m, $l);

        $this->assertArrayHasKey('most', $scores);
        $this->assertArrayHasKey('least', $scores);
        $this->assertArrayHasKey('change', $scores);

        $this->assertSame(3, $scores['most']['D']);
        $this->assertSame(1, $scores['most']['I']);
        $this->assertSame(1, $scores['most']['S']);
        $this->assertSame(1, $scores['most']['C']);

        $this->assertSame(0, $scores['least']['D']);
        $this->assertSame(1, $scores['least']['I']);
        $this->assertSame(2, $scores['least']['S']);
        $this->assertSame(2, $scores['least']['C']);

        $this->assertSame(3, $scores['change']['D']);
        $this->assertSame(0, $scores['change']['I']);
        $this->assertSame(-1, $scores['change']['S']);
        $this->assertSame(-1, $scores['change']['C']);
    }

    public function testResolveProfileFallbackWithoutDb(): void
    {
        $change = ['D' => 2, 'I' => 1, 'S' => -2, 'C' => -1];
        $profile = ProfileResolver::resolveProfile($change, null, sys_get_temp_dir());

        $this->assertIsArray($profile);
        $this->assertArrayHasKey('segment', $profile);
        $this->assertArrayHasKey('pattern', $profile);
        $this->assertSame(15, $profile['segment']['d']);
        $this->assertSame(14, $profile['segment']['i']);
        $this->assertSame(15, $profile['segment']['s']);
        $this->assertSame(14, $profile['segment']['c']);
    }

    public function testEvaluateEndToEnd(): void
    {
        $m = array_merge(array_fill(0, 14, 'D'), array_fill(0, 14, 'I'));
        $l = array_merge(array_fill(0, 14, 'S'), array_fill(0, 14, 'C'));

        $result = ProfileResolver::evaluate($m, $l, null, sys_get_temp_dir());

        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('scores', $result);
        $this->assertArrayHasKey('graph', $result);
        $this->assertArrayHasKey('profile', $result);

        $this->assertArrayHasKey('line1_most', $result['graph']);
        $this->assertArrayHasKey('line2_least', $result['graph']);
        $this->assertArrayHasKey('line3_change', $result['graph']);
    }
}
