<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\ToolCommands;
use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit tool commands.
 *
 * @group tool
 */
class ToolCommandsTest extends AbstractTest
{

    /**
     * Data provider for testBuild.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/tool.yml');
    }

    /**
     * Test ToolCommands commands.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testTool(string $command, array $config = [], array $expectations = [])
    {
        $this->markTestIncomplete('Skip test');

        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
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
     * Data provider for command toolkit:opts-review.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProviderOptsReview()
    {
        return $this->getFixtureContent('commands/opts-review.yml');
    }

    /**
     * Test command toolkit:opts-review.
     *
     * @param string $command
     *   A command.
     * @param string $sample
     *   The sample file to use.
     * @param array $config
     *   A configuration.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProviderOptsReview
     */
    public function testOptsReview(string $command, string $sample = '', array $config = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }

        if (!empty($sample)) {
            $this->fs->copy(
                $this->getFixtureFilepath('samples/sample-' . $sample),
                $this->getSandboxFilepath('.opts.yml')
            );
        }

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Fetch the output.
        $content = $output->fetch();
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($content, $expectation);
        }
    }

    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new ToolCommands())->getConfigurationFile());
    }

}
