<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/ScoreCalculator.php';

class DiscTest extends TestCase
{
    public function testCalculateScoresBasic(): void
    {
        $most = ['D', 'D', 'I', 'S', 'C'];
        $least = ['I', 'S', 'S', 'C', 'C'];
        
        $result = \Disc\ScoreCalculator::calculateScores($most, $least);
        
        $this->assertEquals(2, $result['D']);
        $this->assertEquals(0, $result['I']);
    }

    public function testCalculateScoresEmpty(): void
    {
        $result = \Disc\ScoreCalculator::calculateScores([], []);
        
        foreach (['D', 'I', 'S', 'C'] as $dim) {
            $this->assertEquals(0, $result[$dim]);
        }
    }

    public function testCalculateScoresFiltersNonScalar(): void
    {
        $most = ['D', ['array'], 'I'];
        $least = ['S', new stdClass(), 'C'];
        
        // Should not throw warning, arrays filtered out
        $result = \Disc\ScoreCalculator::calculateScores($most, $least);
        
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
        
        $result = \Disc\ScoreCalculator::calculateScores($most, $least);
        
        $this->assertEquals(-2, $result['D']);
    }
}
