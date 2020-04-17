<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ToolkitCommands.
 */
class InstallCommands extends AbstractCommands
{

    use TaskRunnerTasks\Drush\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../../config/commands/install.yml';
    }

    /**
     * Install a clean website.
     *
     * @param array $options
     *   Command options.
     *
     * @command toolkit:install-clean
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
        $has_config = file_exists($options['config-file']);
        $params = $has_config ? ' --existing-config' : '';

        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('./vendor/bin/run toolkit:build-dev')
            ->exec('./vendor/bin/run drupal:site-install' . $params);

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Install a clone website.
     *
     * @command toolkit:install-clone
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function installClone()
    {
        $tasks = [];

        $tasks[] = $this->taskExec('./vendor/bin/run toolkit:install-dump');
        $tasks[] = $this->taskExec('./vendor/bin/run toolkit:run-deploy');

        // Collect and execute list of commands set on local runner.yml.
        $commands = $this->getConfig()->get("toolkit.install.clone.commands");
        if (!empty($commands)) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Import config.
     *
     * @command toolkit:import-config
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function importConfig()
    {
        $tasks = [];

        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('./vendor/bin/drush config:import -y')
            ->exec('./vendor/bin/drush cache:rebuild');

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }
}
