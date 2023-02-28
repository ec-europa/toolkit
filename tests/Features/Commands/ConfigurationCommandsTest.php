<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use EcEuropa\Toolkit\Toolkit;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit configuration commands.
 *
 * @group configuration
 */
class ConfigurationCommandsTest extends AbstractTest
{

    /**
     * Data provider for testConfiguration.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/configuration.yml');
    }

    /**
     * Test ConfigurationCommands commands.
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
    public function testConfiguration(string $command, array $config = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }

        $this->prepareResources($resources);

        // Run command.
        $result = $this->runCommand($command, false);

        if ($command === 'help example:full') {
            if (str_starts_with(Toolkit::getRoboVersion(), '4')) {
                $expectations = $expectations['robo4'];
            } else {
                $expectations = $expectations['robo3'];
            }
        }

//        $this->debugExpectations($result['output'], $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($result['output'], $expectation);
        }
    }

    /**
     * Test ConfigurationCommands 'run' and 'process'.
     */
    public function testConfigurationRunAndProcess()
    {
        $config = [
            'commands' => [
                'test:run' => [
                    ['task' => 'run', 'command' => 'drupal:test-setup'],
                ],
                'drupal:test-setup' => [
                    ['task' => 'process', 'source' => 'test.txt', 'destination' => 'test-output.txt'],
                ],
            ],
        ];
        $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        // This file will be processed.
        file_put_contents($this->getSandboxFilepath('test.txt'), 'The drupal root is ${drupal.root}');
        // Static version to compare.
        file_put_contents($this->getSandboxFilepath('test-static.txt'), 'The drupal root is web');

        // Run command.
        $input = new StringInput('test:run --working-dir=' . $this->getSandboxRoot());

        $runner = new Runner($this->getClassLoader(), $input, (new NullOutput()));
        $runner->getConfig()->set('runner.bin_dir', '../../../.');
        $code = $runner->run();

        // Asserts.
        $this->assertEquals(0, $code);
        $this->assertFileExists($this->getSandboxFilepath('runner.yml'));
        $this->assertFileEquals(
            $this->getSandboxFilepath('test-output.txt'),
            $this->getSandboxFilepath('test-static.txt')
        );
    }

    /**
     * Test ConfigurationCommands 'exec'.
     */
    public function testConfigurationExec()
    {
        $config = [
            'commands' => [
                'test:exec' => [
                    ['task' => 'exec', 'command' => 'echo "The drupal root is web" > test-static.txt'],
                ],
            ],
        ];
        $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        // The file to compare.
        file_put_contents($this->getSandboxFilepath('test.txt'), "The drupal root is web\n");

        // Run command.
        $result = $this->runCommand('test:exec', false);

        // Asserts.
        $this->assertEquals(0, $result['code']);
        $this->assertFileExists($this->getSandboxFilepath('runner.yml'));
        $this->assertFileEquals(
            $this->getSandboxFilepath('test.txt'),
            $this->getSandboxFilepath('test-static.txt')
        );
        $this->assertStringStartsWith(
            ' [Exec] Running echo "The drupal root is web" > test-static.txt',
            $result['output']
        );
    }

    /**
     * Tests configuration overrides.
     */
    public function testConfigOverride(): void
    {
        $runnerDistConfig = [
            'foo' => [
                'bar' => 'baz',
                'qux' => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
            ],
        ];
        $this->fs->dumpFile($this->getSandboxFilepath('runner.yml.dist'), Yaml::dump($runnerDistConfig));
        $arbitraryYamlConfig = [
           'color' => 'red',
        ];
        $this->fs->dumpFile($this->getSandboxFilepath('config/runner/colors.yml'), Yaml::dump($arbitraryYamlConfig));
        $runnerConfig = [
            'foo' => [
                'qux' => [
                    'key1' => 'value999',
                ],
            ],
            'color' => 'yellow',
        ];
        $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($runnerConfig));

        $expectedFooConfig = <<<YAML
        bar: baz
        qux:
          key1: value999
          key2: value2
        YAML;
        $result = $this->runCommand('config foo', false);
        $this->assertSame($expectedFooConfig, trim($result['output']));

        $result = $this->runCommand('config color', false);
        $this->assertSame('yellow', trim($result['output']));
    }
}
