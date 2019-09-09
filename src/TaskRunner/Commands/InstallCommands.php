<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ToolkitCommands.
 */
class InstallCommands extends AbstractCommands {

  use TaskRunnerTasks\Drush\loadTasks;

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFile() {
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
  ]) {
    $tasks = [];

    // Install site from existing configuration, if available.
    $has_config = file_exists($options['config-file']);
    $params = $has_config ? ' --existing-config' : '';
    $tasks[] = $this->taskExec('./vendor/bin/run drupal:site-install' . $params);

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Install a clone website.
   *
   * @param array $options
   *   Command options.
   *
   * @command toolkit:install-clone
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   */
  public function installClone(array $options = [
    'dumpfile' => InputOption::VALUE_REQUIRED,
    'config-file' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];

    $has_dump = file_exists($options['dumpfile']);
    $has_config = file_exists($options['config-file']);

    // If ASDA snapshot is available then we will restore it.
    if ($has_dump) {
      $tasks[] = $this->taskExec('./vendor/bin/run toolkit:install-dump');

      // If also configuration is present then we will import it.
      if ($has_config) {
        $tasks[] = $this->taskExec('./vendor/bin/run toolkit:import-config');
      }
    }
    // If no ASDA snapshot is present then we will install a fresh copy.
    else {
      // Install site from existing configuration, if available.
      $params = $has_config ? ' --existing-config' : '';
      $tasks[] = $this->taskExec('./vendor/bin/run drupal:site-install' . $params);
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
  public function importConfig() {
    $tasks = [];

    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/drush config:import -y')
      ->exec('./vendor/bin/drush cache:rebuild');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
