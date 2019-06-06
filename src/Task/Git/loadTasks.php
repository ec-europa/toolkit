<?php

namespace EcEuropa\Toolkit\Task\Git;

/**
 * Task loader trait.
 */
trait loadTasks {

  /**
   * Build and return CheckoutBranch task.
   *
   * @param string $branchName
   *   Name of the branch.
   *
   * @return \EcEuropa\Toolkit\Task\Git\CheckoutBranch
   *   Task instance.
   */
  protected function taskCheckoutBranch($branchName) {
    return $this->task(CheckoutBranch::class, $branchName);
  }

}
