<?php
declare(strict_types=1);

namespace Disc\Tests;

use PHPUnit\Framework\TestCase;

class ApiScoreTest extends TestCase
{
    private function runEndpoint(string $method = 'POST', ?string $body = null, array $post = []): array
    {
        $backupServer = $_SERVER;
        $backupPost = $_POST;

        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/tests/ApiScoreTest.php';
        $_POST = $post;

        // Custom stream wrapper or temp file for php://input simulation when body is provided
        if ($body !== null) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'api_input_');
            file_put_contents($tmpFile, $body);
            // In CLI PHP, php://input reads from standard input or stream,
            // so we test JSON payloads via POST or direct validator + endpoint runner
        }

        ob_start();
        http_response_code(200); // reset
        require dirname(__DIR__) . '/api/score.php';
        $output = ob_get_clean();
        $code = http_response_code();

        $_SERVER = $backupServer;
        $_POST = $backupPost;

        $decoded = json_decode((string)$output, true);

        return [
            'code' => $code,
            'output' => (string)$output,
            'json' => is_array($decoded) ? $decoded : null,
        ];
    }

    public function testRejectsGetMethod(): void
    {
        $res = $this->runEndpoint('GET');

        $this->assertSame(405, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertSame('error', $res['json']['status']);
        $this->assertSame('Method Not Allowed', $res['json']['error']);
    }

    public function testValidationFailureReturns400(): void
    {
        $res = $this->runEndpoint('POST', null, ['answers' => []]);

        $this->assertSame(400, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertSame('error', $res['json']['status']);
        $this->assertSame('Bad Request', $res['json']['error']);
        $this->assertNotEmpty($res['json']['errors']);
    }

    public function testSuccessfulEvaluationReturns200WithPostPayload(): void
    {
        $dims = ['D', 'I', 'S', 'C'];
        $answers = [];
        for ($i = 0; $i < 28; $i++) {
            $answers[] = [
                'most' => $dims[$i % 4],
                'least' => $dims[($i + 1) % 4],
            ];
        }

        $res = $this->runEndpoint('POST', null, ['answers' => $answers]);

        $this->assertSame(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertSame('success', $res['json']['status']);
        $this->assertArrayHasKey('scores', $res['json']);
        $this->assertArrayHasKey('graph', $res['json']);
        $this->assertArrayHasKey('profile', $res['json']);
        $this->assertArrayHasKey('segment', $res['json']);

        $this->assertSame(7, $res['json']['scores']['most']['D']);
        $this->assertSame(7, $res['json']['scores']['least']['I']);
    }
}
