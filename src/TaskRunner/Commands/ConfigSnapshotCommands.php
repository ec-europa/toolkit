<?php

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit as Toolkit;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Configuration snapshot commands.
 */
class ConfigSnapshotCommands extends AbstractCommands implements ContainerAwareInterface {

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
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   *
   * @command toolkit:config-snapshot
   *
   * @option remote
   *   Git remote name.
   * @option strict
   *   Fail command if branch does not have a remote counterpart.
   * @option directory
   *   Configuration directory to be exported.
   * @option message
   *   Commit message.
   */
  public function configSnapshot($branch, array $options = [
    'remote' => 'origin',
    'strict' => FALSE,
    'directory' => './config/sync',
    'message' => '!date: configuration export.',
  ]) {
    $tasks = [];

    // Ensure branch.
    $tasks[] = $this->taskEnsureBranch($branch)
      ->workingDir($options['working-dir'])
      ->remote($options['remote'])
      ->strict($options['strict']);

    // Export configuration.
    $tasks[] = $this->taskExec('drush')
      ->arg('config:export')
      ->option('-y');

    // Prepare Git user name, email and commit message.
    $userName = $this->getConfig()->get('toolkit.git.user_name');
    $userEmail = $this->getConfig()->get('toolkit.git.user_email');
    $message = str_replace('!date', date('Y-m-d H:i:s'), $options['message']);

    // Commit exported configuration without running Git hooks.
    $tasks[] = $this->taskGitStack()
      ->stopOnFail()
      ->silent(TRUE)
      ->env('GIT_AUTHOR_NAME', $userName)
      ->env('GIT_COMMITTER_NAME', $userName)
      ->env('GIT_AUTHOR_EMAIL', $userEmail)
      ->env('GIT_COMMITTER_EMAIL', $userEmail)
      ->dir($options['working-dir'])
      ->add($options['directory'])
      ->commit($message, '-n');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
