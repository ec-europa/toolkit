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
    public static function dataProvider()
    {
        return self::getFixtureContent('commands/component-check.yml');
    }

    /**
     * Test ComponentCheckCommands command.
     *
     * @param string $command
     *   A command.
     * @param array $configuration
     *   A configuration.
     * @param array $variables
     *   Environment variables to set.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testComponentCheck(string $command, array $configuration = [], array $variables = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($configuration)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($configuration));
        }

        if (!empty($variables)) {
            foreach ($variables as $variable) {
                putenv($variable);
            }
        }

        $this->prepareResources($resources);

        // Run command.
        $result = $this->runCommand($command);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($result['output'], $expectation);
        }
    }

    /**
     * Test if configuration file exists.
     */
    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new ComponentCheckCommands())->getConfigurationFile());
    }

}
