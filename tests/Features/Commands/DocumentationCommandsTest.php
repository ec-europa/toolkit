<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\DocumentationCommands;
use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test Documentation commands.
 *
 * @group toolkit
 */
class DocumentationCommandsTest extends AbstractTest
{

    /**
     * Data provider for testToolkit.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/documentation.yml');
    }

    /**
     * Test ToolkitCommands commands.
     *
     * @param string $command
     *   A command.
     * @param array $resources
     *   Resources needed for the test.
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testDocumentation(string $command, array $resources = [], array $expectations = [])
    {
        $this->prepareResources($resources);

        // Run command.
        $result = $this->runCommand($command);
//        $this->debugExpectations($result['output'], $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($result['output'], $expectation);
        }
    }

    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new DocumentationCommands())->getConfigurationFile());
    }

}
