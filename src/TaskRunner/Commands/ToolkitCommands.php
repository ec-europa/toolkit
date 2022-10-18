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
     *
     * @command toolkit:version-write
     *
     * @hidden
     */
    public function toolkitVersionWrite(string $version)
    {
        return $this->taskReplaceInFile(Toolkit::getToolkitRoot() . '/src/Toolkit.php')
            ->regex("#VERSION = '[^']*'#")
            ->regex("#VERSION = '[^']*'#")
            ->to("VERSION = '" . $version . "'")
            ->run();
    }

    /**
     * Write the release changelog to the CHANGELOG.md file.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:changelog-write
     *
     * @hidden
     */
    public function toolkitChangelogWrite(string $version)
    {
        throw new AbortTasksException('@TODO This task needs to be implemented.');
        $this->taskChangelog()
            ->setHeader("## Version 8.5.1\n")
            ->version($version)
            ->change("## Version 9.0.0\nReleased to github")
            ->run();
    }

    /**
     * Create a release for Toolkit.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:release
     *
     * @hidden
     */
    public function toolkitRelease(string $version)
    {
        throw new AbortTasksException('@TODO This task needs to be implemented.');
        // Call the toolkit:version-write and toolkit:changelog-write and create tag.
        $tasks = [];
        $runner_bin = $this->getBin('run');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec("$runner_bin toolkit:version-write $version")
            ->exec("$runner_bin toolkit:changelog-write $version");

        $tasks[] = $this->taskGitStack()
            ->stopOnFail()
            ->add('-A')
            ->commit("Prepare release $version")
            ->push('origin', 'master')
            ->tag($version)
            ->push('origin', $version);

        return $this->collectionBuilder()->addTaskList($tasks);
    }
}
