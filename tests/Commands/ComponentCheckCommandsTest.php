<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;

class ComponentCheckCommandsTest extends AbstractTest
{

    /**
     * Data provider for testComponentCheck.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/component-check.yml');
    }

    /**
     * Test ComponentCheckCommands command.
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
    public function testComponentCheck(string $command, array $config = [], array $expected = [])
    {
        $this->markTestSkipped('Skip test');
    }

}
