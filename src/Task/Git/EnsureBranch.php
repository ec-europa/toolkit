<?php

namespace EcEuropa\Toolkit\Task\Git;

use Gitonomy\Git\Repository;
use Robo\Contract\BuilderAwareInterface;
use Robo\ResultData;
use Robo\Task\BaseTask;
use Robo\TaskAccessor;

/**
 * Task that creates a given branch locally and sets related tracking remote.
 */
class EnsureBranch extends BaseTask implements BuilderAwareInterface {

  use \Robo\Task\Vcs\loadTasks;
  use TaskAccessor;

  /**
   * Branch name.
   *
   * @var string
   */
  protected $branchName;

  /**
   * Remote name, default to 'origin'.
   *
   * @var string
   */
  protected $remote = 'origin';

  /**
   * Current working directory.
   *
   * @var string
   */
  protected $workingDir = '.';

  /**
   * When true fail task if local branch does not have a remote counterpart.
   *
   * @var bool
   */
  protected $strict = FALSE;

  /**
   * EnsureBranch constructor.
   *
   * @param string $branchName
   *   Branch name.
   */
  public function __construct($branchName) {
    $this->branchName = $branchName;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    $tasks = [];

    if ($this->strict) {
      $this->printTaskDebug('Running in strict mode.');
    }

    // Fail a command ran in strict mode if remote branch does not exist.
    if ($this->strict && !$this->hasRemoteBranch()) {
      $message = "Remote branch '$this->branchName' does not exists.";
      $this->printTaskError($message);
      return new ResultData(ResultData::EXITCODE_ERROR, $message);
    }

    $command = [
      '--work-tree', $this->workingDir,
      'checkout', '-b', $this->branchName,
    ];
    if ($this->hasRemoteBranch()) {
      $command += ['--track', "$this->remote/$this->branchName"];
    }

    $tasks[] = $this->taskGitStack()->exec($command);

    return $this->collectionBuilder()->addTaskList($tasks)->run();
  }

  /**
   * Set remote name, defaults to 'origin'.
   *
   * @param string $remote
   *   Remote name.
   *
   * @return EnsureBranch
   *   Current task object.
   */
  public function remote(string $remote): EnsureBranch {
    $this->remote = $remote;
    return $this;
  }

  /**
   * Set working directory.
   *
   * @param string $workingDir
   *   Working directory.
   *
   * @return EnsureBranch
   *   Current task object.
   */
  public function workingDir(string $workingDir): EnsureBranch {
    $this->workingDir = $workingDir;
    return $this;
  }

  /**
   * Enable strict mode.
   *
   * @param bool $strict
   *   Strict mode.
   *
   * @return \EcEuropa\Toolkit\Task\Git\EnsureBranch
   *   Current task object.
   */
  public function strict(bool $strict): EnsureBranch {
    $this->strict = $strict;
    return $this;
  }

  /**
   * Initialize and get Git repository.
   *
   * @return \Gitonomy\Git\Repository
   *   Git repository instance.
   */
  protected function getRepository(): Repository {
    return new Repository($this->workingDir, [
      'debug'  => TRUE,
      'logger' => $this->logger,
    ]);
  }

  /**
   * Check if remote branch exists.
   *
   * @return bool
   *   Check whereas related remote branch exists.
   */
  protected function hasRemoteBranch(): bool {
    return $this->getRepository()
      ->getReferences()
      ->hasRemoteBranch("{$this->remote}/{$this->branchName}");
  }

}
