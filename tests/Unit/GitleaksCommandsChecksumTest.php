<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Unit;

use EcEuropa\Toolkit\TaskRunner\Commands\GitleaksCommands;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit tests for Gitleaks checksum handling.
 */
#[Group('gitleaks')]
class GitleaksCommandsChecksumTest extends TestCase
{

    /**
     * Test method GitleaksCommands::isChecksumValid().
     */
    public function testIsChecksumValid(): void
    {
        $method = new ReflectionMethod(
            GitleaksCommands::class,
            'isChecksumValid'
        );
        $method->setAccessible(true);

        $content = 'sample-archive-content';
        $validSha = hash('sha256', $content);

        $command = new GitleaksCommands();

        $this->assertTrue($method->invoke($command, $content, $validSha));
        $this->assertFalse($method->invoke($command, $content, str_repeat('0', 64)));
        $this->assertFalse($method->invoke($command, $content, ''));
    }

}
