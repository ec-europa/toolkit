<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Unit;

use Composer\Autoload\ClassLoader;
use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Yaml\Yaml;

/**
 * Unit tests for ProtectedCommandGuard.
 */
class ProtectedCommandsTest extends AbstractTest
{
    private ?ClassLoader $testLoader = null;

    /**
     * {@inheritdoc}
     */
    protected function getClassLoader(): ClassLoader
    {
        $this->testLoader = clone parent::getClassLoader();
        $this->testLoader->addPsr4('TestProject\\', $this->getSandboxFilepath('src'));
        $this->testLoader->register();

        return $this->testLoader;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        if ($this->testLoader !== null) {
            $this->testLoader->unregister();
            $this->testLoader = null;
        }

        parent::tearDown();
    }

    /**
     * Test that overriding a protected command via runner.yml is blocked.
     */
    public function testRunnerYmlOverrideIsBlocked(): void
    {
        $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump([
            'commands' => [
                'toolkit:build-dist' => [
                    'tasks' => [
                        ['task' => 'exec', 'command' => "echo 'Should not run'"],
                    ],
                ],
            ],
        ]));

        $result = $this->runCommand('toolkit:build-dist');

        $this->assertNotEquals(0, $result['code']);
        $this->assertStringContainsString(
            'Command "toolkit:build-dist" is protected by ec-europa/toolkit and cannot be overridden via runner.yml.',
            $result['output']
        );
        $this->assertStringNotContainsString('Should not run', $result['output']);
    }

    /**
     * Test that @hook replace-command on a protected command is blocked.
     */
    public function testReplaceCommandHookIsBlocked(): void
    {
        $this->fs->dumpFile(
            $this->getSandboxFilepath('src/TaskRunner/Commands/ProjectCommands.php'),
            <<<'PHP'
<?php

namespace TestProject\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;

class ProjectCommands extends AbstractCommands
{
    /**
     * @hook replace-command toolkit:build-dist
     */
    public function replaceBuildDist()
    {
        $this->writeln('Replaced build-dist executed');
    }
}
PHP
        );

        $result = $this->runCommand('toolkit:build-dist');

        $this->assertNotEquals(0, $result['code']);
        $this->assertStringContainsString(
            'Command "toolkit:build-dist" is protected by ec-europa/toolkit and cannot be overridden (found @hook replace-command in TestProject\TaskRunner\Commands\ProjectCommands).',
            $result['output']
        );
        $this->assertStringNotContainsString('Replaced build-dist executed', $result['output']);
    }

    /**
     * Test that redefining a protected @command in a project class is blocked.
     */
    public function testDuplicateCommandDefinitionIsBlocked(): void
    {
        $this->fs->dumpFile(
            $this->getSandboxFilepath('src/TaskRunner/Commands/DuplicateCommands.php'),
            <<<'PHP'
<?php

namespace TestProject\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;

class DuplicateCommands extends AbstractCommands
{
    /**
     * @command toolkit:build-dist
     */
    public function buildDist()
    {
        $this->writeln('Duplicate build-dist executed');
    }
}
PHP
        );

        $result = $this->runCommand('toolkit:build-dist');

        $this->assertNotEquals(0, $result['code']);
        $this->assertStringContainsString(
            'Command "toolkit:build-dist" is protected by ec-europa/toolkit and cannot be overridden (redefined in TestProject\TaskRunner\Commands\DuplicateCommands).',
            $result['output']
        );
        $this->assertStringNotContainsString('Duplicate build-dist executed', $result['output']);
    }

}
