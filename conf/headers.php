<?php
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: default-src 'self';");
}
