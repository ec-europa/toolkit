<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\GitHooksCommands;
use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
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
    public function dataProvider()
    {
        return $this->getFixtureContent('commands/git-hooks.yml');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create .git/hooks folder.
        $this->filesystem->mkdir($this->getSandboxFilepath('.git'));
        $this->filesystem->mkdir($this->getSandboxFilepath('.git/hooks'));

        // Create dummy hooks.
        $this->filesystem->touch($this->getSandboxFilepath('.git/hooks/pre-commit'));
        $this->filesystem->touch($this->getSandboxFilepath('.git/hooks/pre-push'));
    }

    /**
     * Test GitHooksCommands commands.
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
    public function testGitHooks(string $command, array $config = [], array $expectations = [])
    {
        // Setup configuration file.
        file_put_contents($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        // Fetch the output.
        $content = $output->fetch();
//        $this->debugExpectations($content, $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertContainsNotContains($content, $expectation);
        }
    }

    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new GitHooksCommands())->getConfigurationFile());
    }

}
