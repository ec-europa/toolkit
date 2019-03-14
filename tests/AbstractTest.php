<?php

namespace EcEuropa\Toolkit\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Abstract test class for Toolkit commands.
 */
abstract class AbstractTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        if (!is_dir($this->getSandboxRoot())) {
            mkdir($this->getSandboxRoot());
        }
        $filesystem = new Filesystem();
        $filesystem->chmod($this->getSandboxRoot(), 0777, umask(), true);
        $filesystem->remove(glob($this->getSandboxRoot() . '/*'));
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    protected function getClassLoader()
    {
        return require __DIR__.'/../vendor/autoload.php';
    }

    /**
     * @param $filepath
     *
     * @return mixed
     */
    protected function getFixtureContent($filepath)
    {
        return Yaml::parse(file_get_contents(__DIR__."/fixtures/{$filepath}"));
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function getSandboxFilepath($name)
    {
        return $this->getSandboxRoot().'/'.$name;
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function getSandboxRoot()
    {
        return __DIR__."/sandbox";
    }

    /**
     * Helper function to assert contain / not contain expectations.
     *
     * @param string $content
     * @param array  $expected
     */
    protected function assertContainsNotContains($content, array $expected)
    {
        if (!empty($expected['contains'])) {
            $this->assertContains($expected['contains'], $content);
            $this->assertEquals(substr_count($content, $expected['contains']), 1, 'String found more than once.');
        }
        if (!empty($expected['not_contains'])) {
            $this->assertNotContains($expected['not_contains'], $content);
        }
    }
}
