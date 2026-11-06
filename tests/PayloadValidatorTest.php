<?php
declare(strict_types=1);

namespace Disc\Tests;

use PHPUnit\Framework\TestCase;
use Disc\PayloadValidator;
use Disc\ValidationResult;

class PayloadValidatorTest extends TestCase
{
    /**
     * Helper to generate a valid 28-item answers array.
     *
     * @return list<array{most: string, least: string}>
     */
    private function generateValidAnswers(int $count = 28): array
    {
        $answers = [];
        $dims = ['D', 'I', 'S', 'C'];
        for ($i = 0; $i < $count; $i++) {
            $m = $dims[$i % 4];
            $l = $dims[($i + 1) % 4];
            $answers[] = ['most' => $m, 'least' => $l];
        }
        return $answers;
    }

    public function testValidAnswersArrayFormat(): void
    {
        $answers = $this->generateValidAnswers(28);
        $payload = ['answers' => $answers];

        $result = PayloadValidator::validate($payload);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertCount(28, $result->m);
        $this->assertCount(28, $result->l);
        $this->assertSame($answers[0]['most'], $result->m[0]);
        $this->assertSame($answers[0]['least'], $result->l[0]);
    }

    public function testValidDirectArraysFormat(): void
    {
        $answers = $this->generateValidAnswers(28);
        $m = array_column($answers, 'most');
        $l = array_column($answers, 'least');
        $payload = ['m' => $m, 'l' => $l];

        $result = PayloadValidator::validate($payload);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertCount(28, $result->m);
        $this->assertCount(28, $result->l);
    }

    public function testNonArrayPayload(): void
    {
        $result = PayloadValidator::validate('not an array');
        $this->assertFalse($result->isValid);
        $this->assertContains("Payload must be a JSON object or array", $result->errors);

        $resultNull = PayloadValidator::validate(null);
        $this->assertFalse($resultNull->isValid);
    }

    public function testMissingRequiredKeys(): void
    {
        $result = PayloadValidator::validate(['unknown_key' => 123]);
        $this->assertFalse($result->isValid);
        $this->assertContains("Payload must contain 'answers' list or 'm' and 'l' arrays", $result->errors);
    }

    public function testIncorrectItemCount(): void
    {
        $answers = $this->generateValidAnswers(20);
        $payload = ['answers' => $answers];

        $result = PayloadValidator::validate($payload);

        $this->assertFalse($result->isValid);
        $this->assertContains("Expected exactly 28 question items, got 20", $result->errors);
    }

    public function testMissingMostOrLeastInItem(): void
    {
        $answers = $this->generateValidAnswers(28);
        unset($answers[3]['least']);
        $payload = ['answers' => $answers];

        $result = PayloadValidator::validate($payload);

        $this->assertFalse($result->isValid);
        $this->assertContains("Item at index 3 is missing 'most' or 'least'", $result->errors);
    }

    public function testInvalidDimensionValue(): void
    {
        $answers = $this->generateValidAnswers(28);
        $answers[5]['most'] = 'X';
        $payload = ['answers' => $answers];

        $result = PayloadValidator::validate($payload);

        $this->assertFalse($result->isValid);
        $this->assertContains("Invalid dimension 'X' at index 5 (must be D, I, S, C, or #)", $result->errors);
    }

    public function testIdenticalMostAndLeast(): void
    {
        $answers = $this->generateValidAnswers(28);
        $answers[7]['most'] = 'D';
        $answers[7]['least'] = 'D';
        $payload = ['answers' => $answers];

        $result = PayloadValidator::validate($payload);

        $this->assertFalse($result->isValid);
        $this->assertContains("Item at index 7 cannot have identical selections for 'most' and 'least'", $result->errors);
    }

    public function testValidateJsonString(): void
    {
        $answers = $this->generateValidAnswers(28);
        $json = json_encode(['answers' => $answers], JSON_THROW_ON_ERROR);

        $result = PayloadValidator::validateJson($json);
        $this->assertTrue($result->isValid);

        $invalidJsonResult = PayloadValidator::validateJson('{malformed json');
        $this->assertFalse($invalidJsonResult->isValid);
        $this->assertContains("Invalid JSON payload", $invalidJsonResult->errors);
    }
}
