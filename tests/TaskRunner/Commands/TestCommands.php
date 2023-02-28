<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;

class TestCommands extends \EcEuropa\Toolkit\TaskRunner\AbstractCommands
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
}
