<?php
declare(strict_types=1);
try {
    require_once __DIR__ . '/config.php';
} catch (Throwable $e) {
    error_log($e->getMessage());
}
