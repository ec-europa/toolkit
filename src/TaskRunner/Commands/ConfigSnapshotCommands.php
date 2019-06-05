<?php

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit as Toolkit;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;

/**
 * Configuration snapshot commands.
 */
class ConfigSnapshotCommands extends AbstractCommands implements ContainerAwareInterface {

  use TaskRunnerTasks\CollectionFactory\loadTasks;
  use ContainerAwareTrait;
  use Toolkit\Task\Git\loadTasks;

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFile() {
    return __DIR__ . '/../../../config/commands/config-snapshot.yml';
  }

  /**
   * Export configuration and commit it to given branch.
   *
   * If a remote branch with the same name exists it will check it out with the
   * '--track' option.
   *
   * If a remote branch does not exists it will create one locally and commit
   * the configuration to that one.
   *
   * @param string $branch
   *   Name of the branch to commit configuration export to.
   * @param array $options
   *   Command options.
   *
   * @return \Robo\Collection\CollectionBuilder|ResultData
   *   Collection builder.
   *
   * @command toolkit:take-config-snapshot
   *
   * @option strict
   *   Fail command if branch does not have a remote counterpart.
   */
  public function takeConfigSnapshot($branch, array $options = [
    'remote' => 'origin',
    'strict' => FALSE,
  ]) {
    $tasks = [];

    $tasks[] = $this->taskEnsureBranch($branch)
      ->workingDir($options['working-dir'])
      ->remote($options['remote'])
      ->strict($options['strict']);

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
