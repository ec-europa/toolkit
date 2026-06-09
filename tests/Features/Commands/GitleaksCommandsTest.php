<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\GitleaksCommands;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Gitleaks commands.
 *
 * @group toolkit
 */
class GitleaksCommandsTest extends AbstractTest
{

    /**
     * Data provider for testToolkit.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public static function dataProvider()
    {
        return self::getFixtureContent('commands/gitleaks.yml');
    }

    /**
     * Test ToolkitCommands commands.
     *
     * @param string $command
     *   A command.
     * @param array $configuration
     *   A configuration.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testGitleaks(string $command, array $configuration = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($configuration)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($configuration));
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
        $this->assertFileExists((new GitleaksCommands())->getConfigurationFile());
    }

}
