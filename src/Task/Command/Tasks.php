<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Task\Command;

/**
 * Robo task to Execute given tasks from configuration.
 *
 * phpcs:disable Generic.NamingConventions.TraitNameSuffix.Missing
 */
trait Tasks
{
    /**
     * Execute a command.
     *
     * @param string $name
     *   The name of the command being executed.
     * @param array $tasks
     *   An array with tasks to execute.
     */
    protected function taskExecute(string $name, array $tasks)
    {
        return $this->task(ConfigurationCommand::class, $name, $tasks);
    }
}
