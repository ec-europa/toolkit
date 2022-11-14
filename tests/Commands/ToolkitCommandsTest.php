<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;

class ToolkitCommandsTest extends AbstractTest
{

    /**
     * Data provider for testToolkit.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/toolkit.yml');
    }

    /**
     * Test ToolkitCommands commands.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testToolkit(string $command, array $config = [], array $expectations = [])
    {
        $this->markTestIncomplete('Skip test');
    }

}
