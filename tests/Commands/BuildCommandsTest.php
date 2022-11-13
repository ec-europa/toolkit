<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use EcEuropa\Toolkit\Toolkit;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit build commands.
 *
 * @group build
 */
class BuildCommandsTest extends AbstractTest
{

    /**
     * Data provider for testBuild.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/build.yml');
    }

    /**
     * Test BuildCommands commands.
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
    public function testBuild(string $command, array $config = [], array $expectations = [])
    {
        // Setup configuration file.
        file_put_contents($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        // If option config-file is used, provide the files .opts.yml and core.extensions.yml
        // for commands toolkit:install-clean and toolkit:run-deploy, otherwise make sure
        // the files do not exist.
        if (str_contains($command, '--default-theme')) {
            $this->filesystem->mkdir($this->getSandboxFilepath('code'));
            $this->filesystem->mkdir($this->getSandboxFilepath('code/theme'));
        }

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Assert expectations.
        $content = $output->fetch();
        // Attempt to remove absolute paths to Toolkit and replace with "tk".
        if (str_contains($content, Toolkit::getToolkitRoot())) {
            $content = str_replace(Toolkit::getToolkitRoot(), 'tk', $content);
        }
        $this->debugExpectations($content, $expectations);
        foreach ($expectations as $expectation) {
            $this->assertContainsNotContains($content, $expectation);
        }
    }

}
