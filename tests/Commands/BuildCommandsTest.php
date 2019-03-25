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
    public function buildDistDataProvider()
    {
        return $this->getFixtureContent('commands/build.yml');
    }

    /**
     * Test "toolkit:build-dist" command.
     *
     * @param array $config
     * @param array $expected
     *
     * @dataProvider buildDistDataProvider
     */
    public function testBuildDist(array $config, array $expected)
    {
        // Setup test Task Runner configuration file.
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        // Run command.
        $input = new StringInput('toolkit:build-dist --simulate --working-dir=' . $this->getSandboxRoot());
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
