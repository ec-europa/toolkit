<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests;

use EcEuropa\Toolkit\TaskRunner\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Abstract test class for Toolkit commands.
 */
abstract class AbstractTest extends TestCase
{
    /**
     * A Filesystem object.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    public Filesystem $fs;

    /**
     * {@inheritdoc}
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->fs = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (!is_dir($this->getSandboxRoot())) {
            $this->fs->mkdir($this->getSandboxRoot());
        }
        $this->fs->chmod($this->getSandboxRoot(), 0777, umask(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->fs->remove(glob($this->getSandboxRoot() . '/{,.}[!.,!..]*', GLOB_BRACE));
    }

    /**
     * Helper function to do dynamic assertions.
     *
     * @param string $content
     *   Content to test.
     * @param array $expected
     *   Content expected.
     */
    protected function assertDynamic(string $content, array $expected)
    {
        if (!empty($expected['contains'])) {
            $this->assertContains($this->trimEachLine($expected['contains']), [$this->trimEachLine($content)]);
            $this->assertEquals(
                1,
                substr_count($this->trimEachLine($content), $this->trimEachLine($expected['contains'])),
                'String found more than once.'
            );
        }

        if (!empty($expected['not_contains'])) {
            $this->assertNotContains($this->trimEachLine($expected['not_contains']), [$this->trimEachLine($content)]);
        }

        if (!empty($expected['string_contains'])) {
            $this->assertStringContainsString($this->trimEachLine($expected['string_contains']), $this->trimEachLine($content));
        }

        if (!empty($expected['not_string_contains'])) {
            $this->assertStringNotContainsString($this->trimEachLine($expected['not_string_contains']), $this->trimEachLine($content));
        }

        if (!empty($expected['file_expected']) && !empty($expected['file_actual'])) {
            $this->assertFileEquals($expected['file_expected'], $expected['file_actual']);
        }
    }

    /**
     * Prepare given resources.
     *
     * Currently, accepts:
     * ```
     * - from: source.yml
     *   to: destination.yml
     * - mkdir: test-folder
     * - touch: test-folder/touched.txt
     * - file: test.txt
     *   content: |
     *     Some content to add
     * ```
     *
     * @param array $resources
     *   An array with resources to process.
     */
    protected function prepareResources(array $resources)
    {
        foreach ($resources as $resource) {
            if (isset($resource['from'], $resource['to'])) {
                $this->fs->copy(
                    $this->getFixtureFilepath('samples/' . $resource['from']),
                    $this->getSandboxFilepath($resource['to'])
                );
            } elseif (isset($resource['mkdir'])) {
                $this->fs->mkdir($this->getSandboxFilepath($resource['mkdir']));
            } elseif (isset($resource['touch'])) {
                $this->fs->touch($this->getSandboxFilepath($resource['touch']));
            } elseif (isset($resource['file'], $resource['content'])) {
                $this->fs->dumpFile(
                    $this->getSandboxFilepath($resource['file']),
                    $resource['content']
                );
            }
        }
    }

    /**
     * Execute given command.
     *
     * @param string $command
     *   The command to execute.
     * @param bool $simulate
     *   Whether use --simulate.
     * @param bool $output
     *   Whether to output.
     *
     * @return array
     *   An array keyed by 'code' and 'output'.
     */
    public function runCommand(string $command, bool $simulate = true, bool $output = true): array
    {
        $simulation = $simulate ? ' --simulate' : '';
        $outputObject = $output ? new BufferedOutput() : new NullOutput();

        $input = new StringInput($command . $simulation . ' --working-dir=' . $this->getSandboxRoot());
        $runner = new Runner($this->getClassLoader(), $input, $outputObject);

        return [
            'code' => $runner->run(),
            'output' => $output ? $outputObject->fetch() : '',
        ];
    }

    /**
     * Helper function to debug the expectations and the content before assert.
     *
     * To debug, set the variable TOOLKIT_DEBUG_EXPECTATIONS to true in the phpunit.xml file.
     *
     * @param string $content
     *   Content to test.
     * @param array $expectations
     *   Array with expectations.
     */
    protected function debugExpectations(string $content, array $expectations)
    {
        if (!getenv('TOOLKIT_DEBUG_EXPECTATIONS')) {
            return;
        }
        $output = "\n-- Content --\n$content\n-- End Content --\n";
        foreach ($expectations as $expectation) {
            if (!empty($expectation['contains'])) {
                $output .= "-- Contains --\n{$expectation['contains']}\n-- End Contains --\n";
            }
            if (!empty($expectation['not_contains'])) {
                $output .= "-- NotContains --\n{$expectation['not_contains']}\n-- End NotContains --\n";
            }
            if (!empty($expectation['string_contains'])) {
                $output .= "-- String --\n{$expectation['string_contains']}\n-- End String --\n";
            }
            if (!empty($expectation['not_string_contains'])) {
                $output .= "-- NotString --\n{$expectation['not_string_contains']}\n-- End NotString --\n";
            }
            if (!empty($expectation['file_expected']) && !empty($expectation['file_actual'])) {
                $output .= "-- Files equal - expected --\n";
                $output .= file_get_contents($expectation['file_expected']);
                $output .= "\n-- END expected --\n-- Files equal - actual --\n";
                $output .= file_get_contents($expectation['file_actual']);
                $output .= "\n-- END actual --\n";
            }
        }
        echo $output;
    }

    /**
     * Trim each line of a blob of text, used when asserting on multiline strings.
     *
     * @param string $text
     *   Untrimmed text.
     *
     * @return string
     *   Trimmed text.
     */
    protected function trimEachLine(string $text): string
    {
        return trim(implode(PHP_EOL, array_map('trim', explode(PHP_EOL, $text))));
    }

    /**
     * Get class loader.
     *
     * @return \Composer\Autoload\ClassLoader
     *   Class loader of vendor.
     */
    protected function getClassLoader()
    {
        return require __DIR__ . '/../vendor/autoload.php';
    }

    /**
     * Get fixture root.
     *
     * @return string
     *   The filepath of fixtures.
     */
    protected static function getFixtureRoot(): string
    {
        return __DIR__ . '/fixtures';
    }

    /**
     * Get fixture filepath.
     *
     * @param string $name
     *   A name of Sandbox.
     *
     * @return string
     *   The filepath of the sandbox file.
     */
    protected static function getFixtureFilepath(string $name): string
    {
        return self::getFixtureRoot() . '/' . $name;
    }

    /**
     * Get fixture content.
     *
     * @param string $filepath
     *   File path.
     *
     * @return mixed|string
     *   A set of test data.
     */
    protected static function getFixtureContent(string $filepath)
    {
        return Yaml::parse(file_get_contents(self::getFixtureFilepath($filepath)));
    }

    /**
     * Get sandbox filepath.
     *
     * @param string $name
     *   A name of Sandbox.
     *
     * @return string
     *   The filepath of the sandbox file.
     */
    protected static function getSandboxFilepath(string $name): string
    {
        return self::getSandboxRoot() . '/' . $name;
    }

    /**
     * Get sandbox root path.
     *
     * @return string
     *   The filepath of sandbox.
     */
    protected static function getSandboxRoot(): string
    {
        return __DIR__ . '/sandbox/' . self::getClassName();
    }

    /**
     * Returns the current class name.
     *
     * @return string
     *   The class name.
     */
    protected static function getClassName(): string
    {
        $class = explode('\\', static::class);
        return (string) end($class);
    }

}
