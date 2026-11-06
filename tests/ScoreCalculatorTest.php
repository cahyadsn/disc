<?php
declare(strict_types=1);

namespace Disc\Tests;

use PHPUnit\Framework\TestCase;
use Disc\ScoreCalculator;

class ScoreCalculatorTest extends TestCase
{
    public function testDimensionsConstant(): void
    {
        $this->assertSame(['D', 'I', 'S', 'C'], ScoreCalculator::DIMENSIONS);
    }

    public function testCalculateScoresBasic(): void
    {
        $most = ['D', 'D', 'I', 'S', 'C'];
        $least = ['I', 'S', 'S', 'C', 'C'];

        $result = ScoreCalculator::calculateScores($most, $least);

        $this->assertEquals(2, $result['D']);
        $this->assertEquals(0, $result['I']);
        $this->assertEquals(-1, $result['S']);
        $this->assertEquals(-1, $result['C']);
    }

    public function testCalculateScoresEmpty(): void
    {
        $result = ScoreCalculator::calculateScores([], []);

        foreach (['D', 'I', 'S', 'C'] as $dim) {
            $this->assertEquals(0, $result[$dim]);
        }
    }

    public function testCalculateScoresFiltersNonScalar(): void
    {
        $most = ['D', ['array'], 'I'];
        $least = ['S', new \stdClass(), 'C'];

        $result = ScoreCalculator::calculateScores($most, $least);

        $this->assertEquals(1, $result['D']);
        $this->assertEquals(1, $result['I']);
        $this->assertEquals(-1, $result['S']);
        $this->assertEquals(-1, $result['C']);
    }

    public function testNegativeChangeScore(): void
    {
        $most = ['D'];
        $least = ['D', 'D', 'D'];

        $result = ScoreCalculator::calculateScores($most, $least);

        $this->assertEquals(-2, $result['D']);
    }

    public function testInvalidDimensions(): void
    {
        $most = ['X', 'Y', 'Z'];
        $least = ['X', 'Y', 'Z'];

        $result = ScoreCalculator::calculateScores($most, $least);

        $this->assertEquals(0, $result['D']);
        $this->assertEquals(0, $result['I']);
        $this->assertEquals(0, $result['S']);
        $this->assertEquals(0, $result['C']);
    }
}
