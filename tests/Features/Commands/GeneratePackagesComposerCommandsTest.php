<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\GeneratePackagesComposerCommands;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit generate packages composer.json commands.
 *
 * @group dump
 */
class GeneratePackagesComposerCommandsTest extends AbstractTest
{

    /**
     * Data provider for testGitHooks.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public static function dataProvider()
    {
        return self::getFixtureContent('commands/generate-packages-composer.yml');
    }

    /**
     * Test GeneratePackagesComposer commands.
     *
     * @param string $command
     *   A command.
     * @param array $configuration
     *   A configuration.
     * @param array $variables
     *   Environment variables to set.
     * @param array $resources
     *   Resources needed for the test.
     * @param bool $simulate
     *   Whether execute the command in simulation mode.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testGeneratePackagesComposer(string $command, array $configuration = [], array $variables = [], array $resources = [], bool $simulate = true, array $expectations = [])
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
        $result = $this->runCommand("$command --no-interaction", $simulate);
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
        $this->assertFileExists((new GeneratePackagesComposerCommands())->getConfigurationFile());
    }

}
