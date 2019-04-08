<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;

/**
 * Class TestsCommands.
 */
class TestsCommands extends AbstractCommands implements FilesystemAwareInterface {
  use NuvoleWebTasks\Config\loadTasks;
  use TaskRunnerTasks\CollectionFactory\loadTasks;
  use TaskRunnerTraits\ConfigurationTokensTrait;
  use TaskRunnerTraits\FilesystemAwareTrait;

  /**
   * Run PHP code review.
   *
   * @command toolkit:test-phpcs
   *
   * @aliases tp
   */
  public function toolkitPhpcs() {
    $tasks = [];

    $tasks[] = $this->taskExec('./vendor/bin/grumphp run');

    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Run Behat tests.
   *
   * @command toolkit:test-behat
   *
   * @aliases tb
   */
  public function toolkitBehat() {
    $tasks = [];

    $tasks[] = $this->taskExec('./vendor/bin/behat --strict');

    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
