<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Unit;

use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

class ReplaceBlockTest extends AbstractTest
{

    /**
     * Data provider for testReplaceBlock.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public static function dataProvider()
    {
        return self::getFixtureContent('commands/replace-block.yml');
    }

    /**
     * Test ReplaceBlock task.
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
    public function testReplaceBlock(string $command, array $resources, array $expectations)
    {
        $config = [
            'commands' => [
                'test:clean-block' => [
                    [
                        'task' => 'replace-block',
                        'filename' => 'test.txt',
                        'start' => '{start}',
                        'end' => '{end}',
                    ]
                ],
                'test:replace-block' => [
                    [
                        'task' => 'replace-block',
                        'filename' => 'test.txt',
                        'start' => '{start}',
                        'end' => '{end}',
                        'content' => "\nThis content is new\n",
                    ]
                ],
                'test:replace-block-start-end' => [
                    [
                        'task' => 'replace-block',
                        'filename' => 'test.txt',
                        'start' => '{start}',
                        'end' => '{end}',
                        'content' => "This content is new",
                        'excludeStartEnd' => true,
                    ]
                ],
            ],
        ];
        $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        $this->prepareResources($resources);

        // Run command.
        $result = $this->runCommand($command, false);

        // Assert expectations.
        $this->assertEquals(0, $result['code']);

        $content = file_get_contents($this->getSandboxFilepath('test.txt'));
//        $this->debugExpectations($content, $expectations);
        foreach ($expectations as $expectation) {
            $this->assertDynamic($content, $expectation);
        }
    }

}
