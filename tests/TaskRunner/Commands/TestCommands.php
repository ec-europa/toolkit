<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\Hook;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\Config\Loader\ConfigProcessor;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;

class TestCommands extends AbstractCommands
{
    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile(): string
    {
        return 'test_command_default_config.yml';
    }

    #[Command(name: 'test_command')]
    public function test(): void
    {
    }

    /**
     * @see \EcEuropa\Toolkit\Tests\Features\Commands\ConfigurationCommandsTest::testConfigOverrideScenarios
     */
    #[Hook(type: HookManager::PRE_INITIALIZE, target: '*')]
    public function alterConfig(): void
    {
        $testConfig = $this->getConfig()->get('test_config');
        $testConfig['overridden_in_TestCommands__alterConfig'] = 5;
        $this->getConfig()->set('test_config', $testConfig);
    }
}
