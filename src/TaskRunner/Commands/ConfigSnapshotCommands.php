<?php

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Gitonomy\Git\Repository;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Robo\ResultData;

/**
 * Configuration snapshot commands.
 */
class ConfigSnapshotCommands extends AbstractCommands implements ContainerAwareInterface {

  use TaskRunnerTasks\CollectionFactory\loadTasks;
  use ContainerAwareTrait;

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
    'strict' => FALSE,
  ]) {
    $collection = $this->collectionBuilder();

    $repository = new Repository($options['working-dir'], [
      'debug'  => TRUE,
    ]);
    $repository->setLogger($this->getContainer()->get('logger'));

    // Fail commands ran in strict mode if remote branch does not exist.
    if ($options['strict'] && !$repository->getReferences()->hasRemoteBranch('origin/' . $branch)) {
      $this->io()->error("Remote branch '$branch' does not exists.");
      return new ResultData(ResultData::EXITCODE_ERROR);
    }

    $collection->addCode(function () use ($repository, $branch) {
      $arguments = [];
      if (!$repository->getReferences()->hasBranch($branch)) {
        $arguments[] = '-b';
      }
      $arguments[] = $branch;
      $repository->run('checkout', $arguments);
    });

    // Build and return task collection.
    return $collection;
  }

}
