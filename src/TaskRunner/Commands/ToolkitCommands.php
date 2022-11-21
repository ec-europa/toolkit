<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\Exception\AbortTasksException;
use Symfony\Component\Yaml\Yaml;

class ToolkitCommands extends AbstractCommands
{

    /**
     * Dumps the current configuration.
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

    /**
     * Generate the list of commands in the commands.rst file.
     *
     * @command toolkit:generate-commands-list
     *
     * @hidden
     *
     * @aliases tk-gcl
     */
    public function generateCommandsList()
    {
        // Get the available commands.
        $commands = $this->taskExec($this->getBin('run'))
            ->silent(true)->run()->getMessage();
        // Remove the header part.
        $commands = preg_replace('/((.|\n)*)(Available commands:)/', '\3', $commands);
        // Add spaces to match the .rst format.
        $commands = preg_replace('/^/im', ' ', $commands);

        $start = ".. toolkit-block-commands\n\n.. code-block::\n\n";
        $end = "\n\n.. toolkit-block-commands-end";
        $task = $this->taskReplaceBlock('docs/guide/commands.rst')
            ->start($start)->end($end)->content($commands);
        return $this->collectionBuilder()->addTask($task);
    }

}
