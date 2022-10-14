<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\Robo;

/**
 * Configuration commands are defined in the runner.yml file under 'commands:'.
 */
class ConfigurationCommands extends AbstractCommands
{
    /**
     * Execute a configuration command.
     */
    public function execute()
    {
        $name = $this->input()->getArgument('command');
        /* @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
        $command = Robo::application()->get($name);
        $tasks = $command->getAnnotationData()['tasks'];
        return $this->taskExecute($tasks);
    }
}
