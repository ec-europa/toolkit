<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;

/**
 * Class ToolkitCommands.
 */
class InstallCommands extends AbstractCommands implements FilesystemAwareInterface {
  use NuvoleWebTasks\Config\loadTasks;
  use TaskRunnerTasks\CollectionFactory\loadTasks;
  use TaskRunnerTraits\ConfigurationTokensTrait;
  use TaskRunnerTraits\FilesystemAwareTrait;

  /**
   * Install clean website.
   *
   * This will download the database if none local then proceed to dump and sync
   * the configuration in the following order:
   * - Verify if .tmp/dump.sql or dump.sql exists, if not download it
   *   in .tmp/dump.sql
   * - Import dump.sql in the current installation
   * - Execute cache-rebuild
   * - Check current status of configuration
   * - Import configuration from datastore into activestore.
   *
   * @command toolkit:install-clean-dev
   */
  public function cleanDev() {
    $tasks = [];

    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/run toolkit:build-dev')
      ->exec('./vendor/bin/run drupal:site-install')
      ->exec('./vendor/bin/run toolkit:disable-drupal-cache')
      ->exec('./vendor/bin/run drupal:setup-test');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Install clean website.
   *
   * This will download the database if none local then proceed to dump and sync
   * the configuration in the following order:
   * - Verify if .tmp/dump.sql or dump.sql exists, if not download it
   *   in .tmp/dump.sql
   * - Import dump.sql in the current installation
   * - Execute cache-rebuild
   * - Check current status of configuration
   * - Import configuration from datastore into activestore.
   *
   * @command toolkit:install-clone
   */
  public function clone() {
    $tasks = [];

    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/run toolkit:build-dev')
      ->exec('./vendor/bin/run drupal:site-install')
      ->exec('./vendor/bin/run toolkit:install-dump');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Disable aggregation and clear cache.
   *
   * @command toolkit:disable-drupal-cache
   */
  public function disableDrupalCache() {
    $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/drush -y config-set system.performance css.preprocess 0')
      ->exec('./vendor/bin/drush -y config-set system.performance js.preprocess 0')
      ->exec('./vendor/bin/drush -y cache:rebuild')
      ->run();
  }

}
