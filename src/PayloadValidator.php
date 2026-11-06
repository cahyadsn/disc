<?php
declare(strict_types=1);
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : PayloadValidator.php
DESC         : request payload validator for disc api
AUTHOR       : CAHYA DSN
CREATED DATE : 2026-11-06
UPDATED DATE : 2026-11-06 10:24:00
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

final class PayloadValidator
{
    public const int REQUIRED_ITEM_COUNT = 28;
    public const array ALLOWED_DIMENSIONS = ['D', 'I', 'S', 'C', '#'];

    /**
     * Validate raw JSON string using native PHP 8.3 json_validate
     */
    public static function validateJson(string $json): ValidationResult
    {
        if (!json_validate($json)) {
            return new ValidationResult(false, ['Invalid JSON payload']);
        }

        /** @var mixed $decoded */
        $decoded = json_decode($json, true);

        return self::validate($decoded);
    }

    /**
     * Validate decoded payload array
     */
    public static function validate(mixed $payload): ValidationResult
    {
        if (!is_array($payload)) {
            return new ValidationResult(false, ['Payload must be a JSON object or array']);
        }

        if (isset($payload['answers']) && is_array($payload['answers'])) {
            return self::validateAnswersList($payload['answers']);
        }

        if (isset($payload['m'], $payload['l']) && is_array($payload['m']) && is_array($payload['l'])) {
            return self::validateDirectArrays($payload['m'], $payload['l']);
        }

        return new ValidationResult(false, ["Payload must contain 'answers' list or 'm' and 'l' arrays"]);
    }

    /**
     * @param list<mixed> $answers
     */
    private static function validateAnswersList(array $answers): ValidationResult
    {
        $count = count($answers);
        if ($count !== self::REQUIRED_ITEM_COUNT) {
            return new ValidationResult(
                false,
                ["Expected exactly " . self::REQUIRED_ITEM_COUNT . " question items, got {$count}"]
            );
        }

        $errors = [];
        $m = [];
        $l = [];

        foreach ($answers as $index => $item) {
            if (!is_array($item) || !isset($item['most']) || !isset($item['least'])) {
                $errors[] = "Item at index {$index} is missing 'most' or 'least'";
                continue;
            }

            $most = is_string($item['most']) ? strtoupper(trim($item['most'])) : '';
            $least = is_string($item['least']) ? strtoupper(trim($item['least'])) : '';

            if (!in_array($most, self::ALLOWED_DIMENSIONS, true)) {
                $errors[] = "Invalid dimension '{$item['most']}' at index {$index} (must be D, I, S, C, or #)";
            }
            if (!in_array($least, self::ALLOWED_DIMENSIONS, true)) {
                $errors[] = "Invalid dimension '{$item['least']}' at index {$index} (must be D, I, S, C, or #)";
            }

            if ($most !== '' && $least !== '' && $most === $least) {
                $errors[] = "Item at index {$index} cannot have identical selections for 'most' and 'least'";
            }

            $m[] = $most;
            $l[] = $least;
        }

        return new ValidationResult(empty($errors), $errors, $m, $l);
    }

    /**
     * @param list<mixed> $mArray
     * @param list<mixed> $lArray
     */
    private static function validateDirectArrays(array $mArray, array $lArray): ValidationResult
    {
        $mCount = count($mArray);
        $lCount = count($lArray);

        if ($mCount !== self::REQUIRED_ITEM_COUNT || $lCount !== self::REQUIRED_ITEM_COUNT) {
            $got = $mCount !== self::REQUIRED_ITEM_COUNT ? $mCount : $lCount;
            return new ValidationResult(
                false,
                ["Expected exactly " . self::REQUIRED_ITEM_COUNT . " question items, got {$got}"]
            );
        }

        $errors = [];
        $m = [];
        $l = [];

        for ($i = 0; $i < self::REQUIRED_ITEM_COUNT; $i++) {
            $rawM = $mArray[$i] ?? null;
            $rawL = $lArray[$i] ?? null;

            if ($rawM === null || $rawL === null) {
                $errors[] = "Item at index {$i} is missing 'most' or 'least'";
                continue;
            }

            $most = is_string($rawM) ? strtoupper(trim($rawM)) : '';
            $least = is_string($rawL) ? strtoupper(trim($rawL)) : '';

            if (!in_array($most, self::ALLOWED_DIMENSIONS, true)) {
                $errors[] = "Invalid dimension '{$rawM}' at index {$i} (must be D, I, S, C, or #)";
            }
            if (!in_array($least, self::ALLOWED_DIMENSIONS, true)) {
                $errors[] = "Invalid dimension '{$rawL}' at index {$i} (must be D, I, S, C, or #)";
            }

            if ($most !== '' && $least !== '' && $most === $least) {
                $errors[] = "Item at index {$i} cannot have identical selections for 'most' and 'least'";
            }

            $m[] = $most;
            $l[] = $least;
        }

        return new ValidationResult(empty($errors), $errors, $m, $l);
    }
}
