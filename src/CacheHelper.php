<?php
namespace Disc;

class CacheHelper
{
    /**
     * Ensure a cache directory exists.
     */
    public static function ensureDirectory(string $path, string $logIdentifier, int $mode = 0755): bool
    {
        if (!is_dir($path)) {
            if (!mkdir($path, $mode, true)) {
                error_log("Failed to create cache directory: " . $logIdentifier);
                return false;
            }
        }
        return true;
    }
}
