<?php

use PHPUnit\Framework\TestCase;

class DiscTest extends TestCase
{
    /**
     * Calculate DISC scores from most/least arrays (extracted from result.php logic)
     */
    private function calculateScores(array $most, array $least): array
    {
        $result = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
        foreach ($most as $v) if (is_scalar($v) && isset($result[$v])) $result[$v]++;
        foreach ($least as $v) if (is_scalar($v) && isset($result[$v])) $result[$v]--;
        return $result;
    }

    public function testCalculateScoresBasic(): void
    {
        $most = ['D', 'D', 'I', 'S', 'C'];
        $least = ['I', 'S', 'S', 'C', 'C'];
        
        $result = $this->calculateScores($most, $least);
        
        $this->assertEquals(2, $result['D']);
        $this->assertEquals(0, $result['I']);
    }

    public function testCalculateScoresEmpty(): void
    {
        $result = $this->calculateScores([], []);
        
        foreach (['D', 'I', 'S', 'C'] as $dim) {
            $this->assertEquals(0, $result[$dim]);
        }
    }

    public function testCalculateScoresFiltersNonScalar(): void
    {
        $most = ['D', ['array'], 'I'];
        $least = ['S', new stdClass(), 'C'];
        
        // Should not throw warning, arrays filtered out
        $result = $this->calculateScores($most, $least);
        
        $this->assertEquals(1, $result['D']);
        $this->assertEquals(1, $result['I']);
    }

    public function testXssEscaping(): void
    {
        $malicious = "<script>alert('XSS')</script>";
        $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testNegativeChangeScore(): void
    {
        $most = ['D'];
        $least = ['D', 'D', 'D'];
        
        $result = $this->calculateScores($most, $least);
        
        $this->assertEquals(-2, $result['D']);
    }
}
