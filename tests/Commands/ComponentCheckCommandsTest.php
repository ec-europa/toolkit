<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit component-check command.
 *
 * @group component
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
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testComponentCheck(string $command, array $config = [], array $expectations = [])
    {
        // Setup configuration file.
        file_put_contents($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        $this->filesystem->copy(
            $this->getFixtureFilepath('samples/sample-composer.lock'),
            $this->getSandboxFilepath('composer.lock')
        );

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Fetch the output.
        $content = $output->fetch();
        $this->debugExpectations($content, $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertContainsNotContains($content, $expectation);
        }
    }

}
