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

        // Make sure settings.php doesn't contain development/testing specific
        // settings, as they might conflict with the configurations from the
        // database dump. For example when testing, some dev/test services might
        // be configured in settings.php. But those services are delivered by a
        // module enabled only with the clean installation. Restoring the MySQL
        // dump, where that module is not installed, will make the site crash.
        // Note that we call the command without the --dev argument.
        $tasks[] = $this->taskExec('./vendor/bin/run drupal:settings-setup');

        $tasks[] = $this->taskExec('./vendor/bin/run toolkit:install-dump');
        $tasks[] = $this->taskExec('./vendor/bin/run toolkit:run-deploy');

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
