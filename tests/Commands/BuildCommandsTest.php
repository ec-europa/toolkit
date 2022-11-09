<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
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
     * Test "toolkit:build-*" commands.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration.
     * @param array $expected
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testBuild(string $command, array $config, array $expected)
    {
        // Setup configuration file.
        file_put_contents($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        if (in_array($command, ['toolkit:build-dev --root=web', 'toolkit:build-dist --root=web --dist-root=dist --tag=1.0.0 --sha=aBcDeF --keep=vendor --remove=CHANGELOG.txt'])) {
            $this->markTestSkipped('Skip test');
        }

        // Run command.
        $input = new StringInput($command . ' --simulate');
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Assert expectations.
        $content = $output->fetch();
//        $this->debugExpectations($content, $expectations);
        foreach ($expected as $row) {
            $this->assertContainsNotContains($content, $row);
        }
    }

}
