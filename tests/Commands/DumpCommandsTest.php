<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Commands;

use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit dump commands.
 *
 * @group dump
 */
class DumpCommandsTest extends AbstractTest
{

    /**
     * Data provider for testDump.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/dump.yml');
    }

    /**
     * Test DumpCommands commands.
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
    public function testDump(string $command, array $config = [], array $expectations = [])
    {
        // Setup configuration file.
        file_put_contents($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        if (str_contains($command, 'install-dump')) {
            $this->filesystem->copy(
                $this->getFixtureFilepath('samples/sample-dump.sql.gz'),
                $this->getSandboxFilepath('dump.sql.gz')
            );
        }
        if (str_contains($command, 'download-dump')) {
            $this->filesystem->copy(
                $this->getFixtureFilepath('samples/sample-mysql-latest.sh1'),
                $this->getSandboxFilepath('mysql-latest.sh1')
            );
        }

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();

        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Assert expectations.
        $content = $output->fetch();
        $this->debugExpectations($content, $expectations);
        foreach ($expectations as $expectation) {
            $this->assertContainsNotContains($content, $expectation);
        }
    }

}
