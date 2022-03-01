<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;
use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * @group build
 *
 * Test Toolkit build commands.
 */
class BuildCommandsTest extends AbstractTest
{
    /**
     * Data provider for testBuild.
     *
     * @return array
     *   An array of test data arrays with assertations.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/build.yml');
    }

    /**
     * Test "toolkit:build-*" commands.
     *
     * @param mixed $command
     *   A command.
     * @param array $config
     *   A configuration.
     * @param array $expected
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testBuild($command, array $config, array $expected)
    {
        // Setup test Task Runner configuration file.
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        if (in_array($command, ['toolkit:build-dev --root=web', 'toolkit:build-dist --root=web --dist-root=dist --tag=1.0.0 --sha=aBcDeF --keep=vendor --remove=CHANGELOG.txt'])) {
            $this->markTestSkipped('Skip test');
        }

        // Run command.
        $input = new StringInput($command . ' --simulate');
        $output = new BufferedOutput();
        $runner = new TaskRunner($input, $output, $this->getClassLoader());
        $runner->run();

        // Assert expectations.
        $content = $output->fetch();
//        echo "\n\n-----> $command\n$content\n\n<-----\n";
        foreach ($expected as $row) {
            $this->assertContainsNotContains($content, $row);
        }
    }
}
