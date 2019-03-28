<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ToolkitCommands.
 */
class CloneCommands extends AbstractCommands {

  use TaskRunnerTasks\CollectionFactory\loadTasks;

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFile() {
    return __DIR__ . '/../../../config/commands/clone.yml';
  }

  /**
   * Install clone from production snapshot.
   *
   * This will download the database if none local then proceed to dump and sync
   * the configuration in the following order:
   * - Verify if the dumpfile exists
   * - Import dump.sql in the current installation
   * - Execute cache-rebuild
   * - Import configuration from datastore into activestore.
   *
   * @param array $options
   *   Command options.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   *
   * @command toolkit:clone-site
   *
   * @option uri Drupal uri.
   * @option dumpfile Drupal uri.
   */
  public function cloneSite(array $options = [
    'uri' => InputOption::VALUE_REQUIRED,
    'dumpfile' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];

    // Unzip and dump database file.
    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' sql-drop -y')
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' sqlc < ' . $options['dumpfile'])
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' cim')
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' cr');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
