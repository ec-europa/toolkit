<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\Exception\AbortTasksException;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

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
        $commandDefinition = $this->getConfig()->get("commands.$name");
        $tasks = $commandDefinition['tasks'] ?? $commandDefinition;
        return $this->taskExecute($tasks);
    }

    /**
     * Dumps the current or given configuration.
     *
     * @param string|null $key
     *   Optional configuration key.
     *
     * @command config
     *
     * @return string
     *   The config values.
     *
     * @throws \Robo\Exception\AbortTasksException
     */
    public function config(?string $key = null): string
    {
        if (!$key) {
            $config = $this->getConfig()->export();
        } else {
            if (!$this->getConfig()->has($key)) {
                throw new AbortTasksException("The key '$key' was not found.");
            }
            $config = $this->getConfig()->get($key);
        }

        return trim(Yaml::dump($config, 10, 2));
    }

}
