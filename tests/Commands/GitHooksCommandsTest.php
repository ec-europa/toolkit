<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;

class GitHooksCommandsTest extends AbstractTest
{

    /**
     * Data provider for testGitHooks.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/git-hooks.yml');
    }

    /**
     * Test GitHooksCommands commands.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration.
     * @param array $expected
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testGitHooks(string $command, array $config = [], array $expected = [])
    {
        $this->markTestSkipped('Skip test');
    }

}
