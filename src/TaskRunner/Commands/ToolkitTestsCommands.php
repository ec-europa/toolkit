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
class ToolkitTestsCommands extends AbstractCommands implements FilesystemAwareInterface {
  use NuvoleWebTasks\Config\loadTasks;
  use TaskRunnerTasks\CollectionFactory\loadTasks;
  use TaskRunnerTraits\ConfigurationTokensTrait;
  use TaskRunnerTraits\FilesystemAwareTrait;

  /**
   * Run PHP code review.
   *
   * @command toolkit:phpcs
   *
   * @aliases tp
   */
  public function toolkitPhpcs() {
    return $this->taskExec('./vendor/bin/grumphp run')->run();
  }

  /**
   * Run Behat tests.
   *
   * @command toolkit:behat
   *
   * @aliases tb
   */
  public function toolkitBehat() {
    return $this->taskExec('./vendor/bin/behat --strict');
  }

}
