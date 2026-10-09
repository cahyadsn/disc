<?php

use PHPUnit\Framework\TestCase;

class DiscTest extends TestCase
{
    public function testXssEscaping(): void
    {
        $malicious = "<script>alert('XSS')</script>";
        $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }
}
