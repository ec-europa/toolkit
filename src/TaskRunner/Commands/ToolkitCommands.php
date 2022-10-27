<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\Exception\AbortTasksException;
use Robo\Exception\TaskException;
use Robo\ResultData;
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
        $r = $this->taskExec($this->getBin('run'))
            ->silent(true)->run()->getMessage();
        // Remove the header part.
        $r = preg_replace('/((.|\n)*)(Available commands:)/', '\3', $r);
        // Add spaces to match the .rst format.
        $r = preg_replace('/^/im', '   ', $r);

        $start = ".. toolkit-block-commands\n\n.. code-block::\n\n";
        $end = "\n\n.. toolkit-block-commands-end";
        $task = $this->taskReplaceBlock('docs/guide/commands.rst')
            ->start($start)
            ->end($end)
            ->content($r);
        return $this->collectionBuilder()->addTask($task);
    }

    /**
     * Generate the documentation
     *
     * @command toolkit:generate-documentation
     *
     * @hidden
     *
     * @aliases tk-docs
     *
     * @throws TaskException
     */
    public function toolkitGenerateDocumentation()
    {
        // Download the phar file if do not exist.
        $phpDoc = 'vendor/bin/phpDoc';
        if (!file_exists($phpDoc)) {
            file_put_contents($phpDoc, file_get_contents('https://phpdoc.org/phpDocumentor.phar'));
            if (filesize($phpDoc) <= 0) {
                $this->writeln('Fail to download the phpDocumentor.phar file.');
                return ResultData::EXITCODE_ERROR;
            }
            $this->_chmod($phpDoc, 0755);
        }
        $task = $this->taskExec($this->getBin('phpDoc'));
        return $this->collectionBuilder()->addTask($task);
    }

}
