<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;

class ToolkitReleaseCommandsTest extends AbstractTest
{

    /**
     * Data provider for testToolkitRelease.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/toolkit-release.yml');
    }

    /**
     * Test ToolkitReleaseCommands commands.
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
    public function testToolkitRelease(string $command, array $config = [], array $expected = [])
    {
        $this->markTestSkipped('Skip test');
    }

}
