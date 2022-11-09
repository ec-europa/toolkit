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
 * @group clone
 */
class DumpCommandsTest extends AbstractTest
{

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem->copy(
            $this->getFixtureFilepath('samples/sample-dump.sql'),
            $this->getSandboxFilepath('dump.sql')
        );
    }

    /**
     * Data provider for testClone.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/dump.yml');
    }

    /**
     * Test "toolkit:clone-*" commands.
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
    public function testClone($command, array $config, array $expected)
    {
        // Setup test Task Runner configuration file.
        $configFile = $this->getSandboxFilepath('runner.yml');
        file_put_contents($configFile, Yaml::dump($config));

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();

        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Assert expectations.
        $content = $output->fetch();
        foreach ($expected as $row) {
            $this->assertContainsNotContains($content, $row);
        }
    }

}
