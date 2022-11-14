<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Unit;

use ArgumentCountError;
use EcEuropa\Toolkit\TaskRunner\Commands\GitHooksCommands;
use EcEuropa\Toolkit\Toolkit;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class GitHooksCommandsTest extends TestCase
{

    public function testConvertHookToMethod()
    {
        $method = new ReflectionMethod(
            GitHooksCommands::class,
            'convertHookToMethod'
        );
        $method->setAccessible(true);

        // Test empty hook name.
        $this->assertFalse($method->invoke(new GitHooksCommands(), ''));

        // Test the conversion from hook-name to method.
        $result = $method->invoke(new GitHooksCommands(), 'pre-commit-msg');
        $this->assertEquals('runPreCommitMsg', $result);

        // Test no parameters (Make this test as last to avoid excluding other exceptions).
        $this->expectException(ArgumentCountError::class);
        $this->assertFalse($method->invoke(new GitHooksCommands()));
    }

    public function testHookFiles()
    {
        $method = new ReflectionMethod(
            GitHooksCommands::class,
            'getHookFiles'
        );
        $method->setAccessible(true);

        // Test existence of default hooks.
        $result = $method->invoke(new GitHooksCommands(), Toolkit::getToolkitRoot() . '/resources/git/hooks');
        $this->assertArrayHasKey('pre-commit', $result);
        $this->assertArrayHasKey('pre-push', $result);
        $this->assertArrayHasKey('prepare-commit-msg', $result);

        // Test no parameters.
        $this->expectException(ArgumentCountError::class);
        $this->assertFalse($method->invoke(new GitHooksCommands()));
    }

}
