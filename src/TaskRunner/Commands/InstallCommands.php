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
   * Install a clean website.
   *
   * The installation in the following order:
   * - Prepare the installation
   * - Install the site
   * - Setup files for tests.
   *
   * @command toolkit:install-clean
   */
  public function installClean() {
    $tasks = [];

    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/run toolkit:build-dev')
      ->exec('./vendor/bin/run drupal:site-install')
      ->exec('./vendor/bin/run drupal:setup-test');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Install a clone website.
   *
   * The installation in the following order:
   * - Prepare the installation
   * - Install the site
   * - Setup files for tests
   * - Install a dump database.
   *
   * @command toolkit:install-clone
   */
  public function installClone() {
    $tasks = [];

    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/run toolkit:build-dev')
      ->exec('./vendor/bin/run drupal:site-install')
      ->exec('./vendor/bin/run drupal:setup-test')
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
