<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ToolkitCommands.
 */
class InstallCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/install.yml';
    }

    /**
     * Install a clean website.
     *
     * @param array $options
     *   Command options.
     *
     * @command toolkit:install-clean
     *
     * @aliases tk-iclean
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function installClean(array $options = [
        'config-file' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];

        // Install site from existing configuration, if available.
        $params = file_exists($options['config-file']) ? ' --existing-config' : '';

        $runner_bin = $this->getBin('run');
        $tasks[] = $this->taskExec($runner_bin . ' drupal:site-install' . $params);

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Install a clone website.
     *
     * @command toolkit:install-clone
     *
     * @aliases tk-iclone
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function installClone()
    {
        $tasks = [];

        $runner_bin = $this->getBin('run');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($runner_bin . ' toolkit:install-dump')
            ->exec($runner_bin . ' toolkit:run-deploy');

        // Collect and execute list of commands set on local runner.yml.s
        if (!empty($commands = $this->getConfig()->get('toolkit.install.clone.commands'))) {
            $tasks[] = $this->taskExecute($commands);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Import config.
     *
     * @command toolkit:import-config
     *
     * @aliases tk-ic
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function importConfig()
    {
        $tasks = [];

        $drush_bin = $this->getBin('drush');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($drush_bin . ' config:import -y')
            ->exec($drush_bin . ' cache:rebuild');

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

}
