<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\GitHooksCommands;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Test Toolkit GitHooks commands.
 *
 * @group git-hooks
 */
class GitHooksCommandsTest extends AbstractTest
{

    /**
     * Data provider for testGitHooks.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public static function dataProvider()
    {
        return self::getFixtureContent('commands/git-hooks.yml');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create .git/hooks folder.
        $this->fs->mkdir($this->getSandboxFilepath('.git'));
        $this->fs->mkdir($this->getSandboxFilepath('.git/hooks'));

        // Create dummy hooks.
        $this->fs->touch($this->getSandboxFilepath('.git/hooks/pre-commit'));
        $this->fs->touch($this->getSandboxFilepath('.git/hooks/pre-push'));
        $this->fs->touch($this->getSandboxFilepath('.git/hooks/commit-msg'));
    }

    /**
     * Test GitHooksCommands commands.
     *
     * @param string $command
     *   A command.
     * @param array $configuration
     *   A configuration.
     * @param array $resources
     *    Resources needed for the test
     * @param array $expectations
     *   Tests expected.
     *
     * @dataProvider dataProvider
     */
    public function testGitHooks(string $command, array $configuration = [], array $resources = [], array $expectations = [])
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

    /**
     * Test if configuration file exists.
     */
    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new GitHooksCommands())->getConfigurationFile());
    }

}
