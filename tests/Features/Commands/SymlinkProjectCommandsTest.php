<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Drupal symlink command.
 *
 * @group drupal
 */
class SymlinkProjectCommandsTest extends AbstractTest
{

    /**
     * Data provider for testSymlinkProjectCommands.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public static function dataProvider()
    {
        return self::getFixtureContent('commands/drupal-symlink-project.yml');
    }

    /**
     * Test the drupal:symlink-project command.
     *
     * @param string $command
     *   A command.
     * @param array $configuration
     *   A configuration array.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Test assertions.
     *
     * @dataProvider dataProvider
     */
    public function testSymlinkProjectCommands(string $command, array $configuration = [], array $resources = [], array $expectations = [])
    {
        // Setup configuration file.
        if (!empty($configuration)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($configuration));
        }

        $this->prepareResources($resources);

        // Run command.
        $result = $this->runCommand($command);
        $this->debugExpectations($result['output'], $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($result['output'], $expectation);
        }
    }

}
