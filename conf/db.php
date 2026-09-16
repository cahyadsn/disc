<?php
try {
    require_once __DIR__ . '/config.php';
} catch (Exception $e) {
    error_log($e->getMessage());
}
