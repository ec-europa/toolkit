<?php

namespace EcEuropa\Toolkit\Task\Git;

/**
 * Task loader trait.
 */
trait loadTasks {

  /**
   * Build and return ensure branch task.
   *
   * @param string $branchName
   *   Name of the branch.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Task instance.
   */
  protected function taskEnsureBranch($branchName) {
    return $this->task(EnsureBranch::class, $branchName);
  }

}
