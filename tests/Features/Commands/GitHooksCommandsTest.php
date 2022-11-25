<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands;

use EcEuropa\Toolkit\TaskRunner\Commands\GitHooksCommands;
use EcEuropa\Toolkit\TaskRunner\Runner;
use EcEuropa\Toolkit\Tests\AbstractTest;
use EcEuropa\Toolkit\Toolkit;
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
        $this->fs->mkdir($this->getSandboxFilepath('.git'));
        $this->fs->mkdir($this->getSandboxFilepath('.git/hooks'));

        // Create dummy hooks.
        $this->fs->touch($this->getSandboxFilepath('.git/hooks/pre-commit'));
        $this->fs->touch($this->getSandboxFilepath('.git/hooks/pre-push'));
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
        if (!empty($config)) {
            $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
        }

        // Run command.
        $input = new StringInput($command . ' --simulate --working-dir=' . $this->getSandboxRoot());
        $output = new BufferedOutput();
        $runner = new Runner($this->getClassLoader(), $input, $output);
        $runner->run();

        if ($command === 'toolkit:hooks-run') {
            if (str_starts_with(Toolkit::getRoboVersion(), '4')) {
                $expectations = $expectations['robo4'];
            } else {
                $expectations = $expectations['robo3'];
            }
        }

        // Fetch the output.
        $content = $output->fetch();
//        $this->debugExpectations($content, $expectations);
        // Assert expectations.
        foreach ($expectations as $expectation) {
            $this->assertDynamic($content, $expectation);
        }
    }

    public function testConfigurationFileExists()
    {
        $this->assertFileExists((new GitHooksCommands())->getConfigurationFile());
    }

}
