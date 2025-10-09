<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Collection\CollectionBuilder;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MoodleInstallCommands.
 */
class MoodleInstallCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/install.yml';
    }

    /**
     * Install a moodle website using database dump.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:install
     *
     * @option dumpfile The dump file name.
     *
     * @aliases md-i
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The cloned website task.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function moodleInstallClone(array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ]): CollectionBuilder
    {
        $tasks = [];
        $runnerBin = $this->getBin('run');
        $tasks[] = $this->taskExec($runnerBin)
            ->arg('toolkit:download-dump');
        $tasks[] = $this->taskExec($runnerBin)
            ->arg('moodle:install-dump')
            ->option('dumpfile', $options['dumpfile'], '=');
        $tasks[] = $this->taskExec($runnerBin)
            ->arg('moodle:clear-cache');

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

}
