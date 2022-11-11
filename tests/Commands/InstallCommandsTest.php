<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit install commands.
 *
 * @group install
 */
class InstallCommandsTest extends AbstractTest
{

    /**
     * Data provider for testInstall.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/install.yml');
    }

    /**
     * Test "toolkit:install-*" commands.
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
    public function testInstall(string $command, array $config = [], array $expectations = [])
    {
        // Setup configuration file.
        file_put_contents($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        // If option config-file is used, provide the files .opts.yml and core.extensions.yml
        // for commands toolkit:install-clean and toolkit:run-deploy, otherwise make sure
        // the files do not exist.
        if (str_contains($command, '--config-file')) {
            $this->filesystem->copy(
                $this->getFixtureFilepath('samples/sample-opts.yml'),
                $this->getSandboxFilepath('.opts.yml')
            );
            $this->filesystem->copy(
                $this->getFixtureFilepath('samples/sample-core.extensions.yml'),
                $this->getSandboxFilepath('core.extensions.yml')
            );
        } else {
            $this->filesystem->remove($this->getSandboxFilepath('.opts.yml'));
            $this->filesystem->remove($this->getSandboxFilepath('core.extensions.yml'));
        }

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Assert expectations.
        $content = $output->fetch();
//        $this->debugExpectations($content, $expectations);
        foreach ($expectations as $expectation) {
            $this->assertContainsNotContains($content, $expectation);
        }
    }

}
