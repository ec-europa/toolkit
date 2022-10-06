<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
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
     * Write the specified version string back into the Toolkit.php file.
     *
     * @param string $version
     *   The version to set.
     */
    public function toolkitVersionWrite($version)
    {
        // Write the result to a file.
        return $this->taskReplaceInFile(Toolkit::getToolkitRoot() . '/src/Toolkit.php')
            ->regex("#VERSION = '[^']*'#")
            ->regex("#VERSION = '[^']*'#")
            ->to("VERSION = '" . $version . "'")
            ->run();
    }

    public function testCommand()
    {
        $this->taskProcess('behat.yml.dist', 'behat.yml')->run();
    }
}
