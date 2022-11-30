<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit blackfire commands.
 *
 * @group blackfire
 */
class BlackfireCommandsTest extends AbstractTest
{

    /**
     * Data provider for testBlackfire.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/blackfire.yml');
    }

    /**
     * Test BlackfireCommands commands.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testBlackfire(string $command, array $config = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }
        $this->prepareResources($resources);

        putenv('BLACKFIRE_SERVER_ID=test');
        putenv('BLACKFIRE_SERVER_TOKEN=test');
        putenv('BLACKFIRE_CLIENT_ID=test');
        putenv('BLACKFIRE_CLIENT_TOKEN=test');

        // Run command.
        $result = $this->runCommand($command);
        $this->debugExpectations($result['output'], $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($result['output'], $expectation);
        }
    }

}
