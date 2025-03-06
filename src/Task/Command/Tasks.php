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
     *  @param array<mixed> $tasks
     *   An array with tasks to execute.
     *
     *  @return mixed
     *   mixed.
     */
    protected function taskExecute(array $tasks)
    {
        return $this->task(ConfigurationCommand::class, $tasks);
    }

}
