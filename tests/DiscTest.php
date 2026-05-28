<?php

use PHPUnit\Framework\TestCase;

class DiscTest extends TestCase
{
    /**
     * Calculate DISC scores from most/least arrays (extracted from result.php logic)
     */
    private function calculateScores(array $most, array $least): array
    {
        $mostCounts = array_count_values(array_filter($most, 'is_scalar'));
        $leastCounts = array_count_values(array_filter($least, 'is_scalar'));
        
        $result = [];
        foreach (['D', 'I', 'S', 'C', '#'] as $a) {
            $result[$a]['most'] = $mostCounts[$a] ?? 0;
            $result[$a]['least'] = $leastCounts[$a] ?? 0;
            $result[$a]['change'] = ($a !== '#') ? $result[$a]['most'] - $result[$a]['least'] : 0;
        }
        return $result;
    }

    public function testCalculateScoresBasic(): void
    {
        $most = ['D', 'D', 'I', 'S', 'C'];
        $least = ['I', 'S', 'S', 'C', 'C'];
        
        $result = $this->calculateScores($most, $least);
        
        $this->assertEquals(2, $result['D']['most']);
        $this->assertEquals(0, $result['D']['least']);
        $this->assertEquals(2, $result['D']['change']);
        
        $this->assertEquals(1, $result['I']['most']);
        $this->assertEquals(1, $result['I']['least']);
        $this->assertEquals(0, $result['I']['change']);
    }

    public function testCalculateScoresEmpty(): void
    {
        $result = $this->calculateScores([], []);
        
        foreach (['D', 'I', 'S', 'C'] as $dim) {
            $this->assertEquals(0, $result[$dim]['most']);
            $this->assertEquals(0, $result[$dim]['least']);
            $this->assertEquals(0, $result[$dim]['change']);
        }
    }

    public function testCalculateScoresFiltersNonScalar(): void
    {
        $most = ['D', ['array'], 'I'];
        $least = ['S', new stdClass(), 'C'];
        
        // Should not throw warning, arrays filtered out
        $result = $this->calculateScores($most, $least);
        
        $this->assertEquals(1, $result['D']['most']);
        $this->assertEquals(1, $result['I']['most']);
    }

    public function testXssEscaping(): void
    {
        $malicious = "<script>alert('XSS')</script>";
        $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testHashDimensionChangeAlwaysZero(): void
    {
        $most = ['#', '#', '#'];
        $least = ['#'];
        
        $result = $this->calculateScores($most, $least);
        
        $this->assertEquals(3, $result['#']['most']);
        $this->assertEquals(1, $result['#']['least']);
        $this->assertEquals(0, $result['#']['change']); // Always 0 for '#'
    }

    public function testNegativeChangeScore(): void
    {
        $most = ['D'];
        $least = ['D', 'D', 'D'];
        
        $result = $this->calculateScores($most, $least);
        
        $this->assertEquals(1, $result['D']['most']);
        $this->assertEquals(3, $result['D']['least']);
        $this->assertEquals(-2, $result['D']['change']);
    }
}
