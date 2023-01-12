<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

class SymlinkProjectCommandsTest extends AbstractTest
{

    /**
     * Data provider for testSymlinkProjectCommands.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/drupal-symlink-project.yml');
    }

    /**
     * Test Toolkit drupal commands.
     *
     * @param string $command
     *   A command.
     * @param array $config
     *   A configuration array.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Test assertions.
     *
     * @dataProvider dataProvider
     */
    public function testSymlinkProjectCommands(string $command, array $config = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($config)) {
            $c = Yaml::dump($config);
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), $c);
        }

        $this->prepareResources($resources);

        // Run command.
        $result = $this->runCommand($command);
//        $this->debugExpectations($result['output'], $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($result['output'], $expectation);
        }
    }

}
