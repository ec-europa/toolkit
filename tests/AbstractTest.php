<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests;

use EcEuropa\Toolkit\Website;
use PHPUnit\Framework\TestCase;
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
     * @var Filesystem
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

        self::setUpMock();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->fs->remove(glob($this->getSandboxRoot() . '/{,.}[!.,!..]*', GLOB_BRACE));
    }

    /**
     * Helper function to assert contain / not contain expectations.
     *
     * @param string $content
     *   Content to test.
     * @param array $expected
     *   Content expected.
     */
    protected function assertContainsNotContains(string $content, array $expected)
    {
        if (!empty($expected['contains'])) {
            $this->assertContains($this->trimEachLine($expected['contains']), [$this->trimEachLine($content)]);
            $this->assertEquals(
                substr_count($this->trimEachLine($content), $this->trimEachLine($expected['contains'])),
                1,
                'String found more than once.'
            );
        }

        if (!empty($expected['not_contains'])) {
            $this->assertNotContains($this->trimEachLine($expected['not_contains']), [$this->trimEachLine($content)]);
        }
    }

    /**
     * Prepare given resources.
     *
     * Currently, accepts:
     * ```
     * - from: source.yml
     *   to: destination.yml
     *
     * - mkdir: test-folder
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
            }
        }
    }

    /**
     * Helper function to debug the expectations and the content before assert.
     *
     * @param string $content
     *   Content to test.
     * @param array $expectations
     *   Array with expectations.
     */
    protected function debugExpectations(string $content, array $expectations)
    {
        $debug = "\n-- Content --\n$content\n-- End Content --\n";
        if (!empty($expectations[0]['contains'])) {
            $debug .= "-- Contains --\n{$expectations[0]['contains']}\n-- End Contains --\n";
        }
        if (!empty($expectations['not_contains'])) {
            $debug .= "-- NotContains --\n{$expectations[0]['not_contains']}\n-- End NotContains --\n";
        }
        echo $debug;
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
    protected function getFixtureRoot(): string
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
    protected function getFixtureFilepath(string $name): string
    {
        return $this->getFixtureRoot() . '/' . $name;
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
    protected function getFixtureContent(string $filepath)
    {
        return Yaml::parse(file_get_contents($this->getFixtureFilepath($filepath)));
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
    protected function getSandboxFilepath(string $name): string
    {
        return $this->getSandboxRoot() . '/' . $name;
    }

    /**
     * Get sandbox root path.
     *
     * @return string
     *   The filepath of sandbox.
     */
    protected function getSandboxRoot(): string
    {
        return __DIR__ . '/sandbox';
    }

    /**
     * Set up the mock server.
     *
     * To access the mock directly in the browser, make sure the port is exposed in the docker-compose.yml
     * file and access for example: http://localhost:8080/tests/mock/api/v1/package-reviews.
     */
    public static function setUpMock()
    {
        Website::setUrl(self::getMockBaseUrl() . '/tests/mock');
    }

    /**
     * Return the mock base url.
     *
     * @return string
     *   The mock base url, defaults to web:8080.
     */
    public static function getMockBaseUrl(): string
    {
        return !empty(getenv('VIRTUAL_HOST'))
            ? (string) getenv('VIRTUAL_HOST')
            : 'web:8080';
    }

}
