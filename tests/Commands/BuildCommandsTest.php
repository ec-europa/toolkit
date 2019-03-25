<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;
use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit build commands.
 */
class BuildCommandsTest extends AbstractTest
{
    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return $this->getFixtureContent('commands/build.yml');
    }

    /**
     * Test "toolkit:build-*" commands.
     *
     * @param $command
     * @param array $config
     * @param array $expected
     *
     * @dataProvider buildDataProvider
     */
    public function testBuild($command, array $config, array $expected)
    {
        // Setup test Task Runner configuration file.
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        // Assert expectations.
        $content = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContainsNotContains($content, $row);
        }
    }
}
