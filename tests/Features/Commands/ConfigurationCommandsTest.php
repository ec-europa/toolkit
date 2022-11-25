<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\Task\Command\ConfigurationCommand;
use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Collection\CollectionBuilder;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
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
        $input = new StringInput($command . ' --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Fetch the output.
        $content = $output->fetch();
//        $this->debugExpectations($content, $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($content, $expectation);
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
        $input = new StringInput('test:run --working-dir=' . $this->getSandboxRoot());;

        $runner = new Runner($this->getClassLoader(), $input, (new NullOutput()));
        $runner->getConfig()->set('runner.bin_dir', '../../.');
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
            'runner' => [
                'bin_dir' => '../../.',
            ],
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
        $input = new StringInput('test:exec --working-dir=' . $this->getSandboxRoot());;
        $output = new BufferedOutput();

        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->getConfig()->set('runner.bin_dir', '../../.');
        $code = $runner->run();

        // Asserts.
        $this->assertEquals(0, $code);
        $this->assertFileExists($this->getSandboxFilepath('runner.yml'));
        $this->assertFileEquals(
            $this->getSandboxFilepath('test.txt'),
            $this->getSandboxFilepath('test-static.txt')
        );
        $this->assertStringStartsWith(' [Exec] Running echo "The drupal root is web" > test-static.txt', $output->fetch());
    }

    /**
     * Test ConfigurationCommands 'php-process'.
     */
    private function testConfigurationProcessPhp()
    {}

}
