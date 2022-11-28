<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\TestsCommands;
use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit tests commands.
 *
 * @group tests
 */
class TestsCommandsTest extends AbstractTest
{

    /**
     * Data provider for testTests.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/tests.yml');
    }

    /**
     * Test TestsCommands commands.
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
    public function testTests(string $command, array $config = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }

        $this->prepareResources($resources);

        // Run command.
        $result = $this->runCommand($command);
//        $this->debugExpectations($result['output'], $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($result['output'], $expectation);
        }
    }

    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new TestsCommands())->getConfigurationFile());
    }

}
