<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\DockerCommands;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit Docker commands.
 *
 * @group docker
 */
class DockerCommandsTest extends AbstractTest
{

    /**
     * Data provider for testDockerCommands.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider(): array
    {
        return $this->getFixtureContent('commands/docker.yml');
    }

    /**
     * Data provider for testDockerCommandsDockerComposeContent.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProviderDockerComposeContent(): array
    {
        return $this->getFixtureContent('commands/docker-compose-content.yml');
    }

    /**
     * Test Toolkit docker commands.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration array.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Test assertions.
     *
     * @dataProvider dataProvider
     */
    public function testDockerCommands(string $command, array $config = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
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
     * Test Toolkit docker commands output docker-composer.yml content.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration array.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Test assertions.
     *
     * @dataProvider dataProviderDockerComposeContent
     */
    public function testDockerCommandsComposeContent(string $command, array $config, array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }

        $this->prepareResources($resources);

        // Run command.
        $this->runCommand($command, false);

        // Assert expectations.
        foreach ($expectations as $expectation) {
            $content = file_get_contents($this->getSandboxFilepath($expectation['file']));
            $this->assertDynamic($content, $expectation);
        }
    }

    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new DockerCommands())->getConfigurationFile());
    }

}
