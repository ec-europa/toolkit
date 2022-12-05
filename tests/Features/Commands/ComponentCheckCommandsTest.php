<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\ComponentCheckCommands;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit component-check command.
 *
 * @group component-check
 */
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
     * @param string $tokens
     *   Tokens to set in the commit message.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testComponentCheck(string $command, array $config = [], string $tokens = '', array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }

        if (!empty($tokens)) {
            putenv('CI_COMMIT_MESSAGE=' . $tokens);
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
        $this->assertFileExists((new ComponentCheckCommands())->getConfigurationFile());
    }

}
