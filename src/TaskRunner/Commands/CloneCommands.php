<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ToolkitCommands.
 */
class CloneCommands extends AbstractCommands {

  use TaskRunnerTasks\CollectionFactory\loadTasks;

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFile() {
    return __DIR__ . '/../../../config/commands/clone.yml';
  }

  /**
   * Install clone from production snapshot.
   *
   * This will download the database if none local then proceed to dump and sync
   * the configuration in the following order:
   * - Verify if the dumpfile exists
   * - Import dump.sql in the current installation
   * - Execute cache-rebuild
   * - Import configuration from datastore into activestore.
   *
   * @param array $options
   *   Command options.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   *
   * @command toolkit:clone-site
   *
   * @option uri Drupal uri.
   * @option dumpfile Drupal uri.
   */
  public function cloneSite(array $options = [
    'uri' => InputOption::VALUE_REQUIRED,
    'dumpfile' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];

    if (!file_exists($options['dumpfile'])) {
      $tasks[] = $this->taskExecStack()
        ->stopOnFail()
        ->exec('./vendor/bin/run toolkit:download-dump --dumpfile=' . $options['dumpfile']);
    }

    // Unzip and dump database file.
    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' sql-drop -y')
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' sqlc < ' . $options['dumpfile'])
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' cim')
      ->exec('vendor/bin/drush --uri=' . $options['uri'] . ' cr');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Download production snapshot.
   *
   * In order to make use of this functionality you must add your
   * ASDA credentials to your environment like. If the credentials
   * are not there you will be prompted to insert them.
   *
   * @param array $options
   *   Command options.
   *
   * @command toolkit:download-dump
   *
   * @return \Robo\Collection\CollectionBuilder|void
   *   Collection builder.
   */
  public function downloadDump(array $options = [
    'asda_url' => InputOption::VALUE_REQUIRED,
    'asda_user' => InputOption::VALUE_REQUIRED,
    'asda_password' => InputOption::VALUE_REQUIRED,
    'dumpfile' => InputOption::VALUE_REQUIRED,
    'project_id' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];

    // Check credentials.
    if ($options['asda_user'] === '${env.ASDA_USER}' || $options['asda_password'] === '${env.ASDA_PASSWORD}') {
      $this->say("The credentials for access ASDA are not found in your env.");

      return $this->collectionBuilder()->addTaskList($tasks);
    }

    $requestUrl = $options['asda_url'] . '/' . $options['project_id'];

    // Download the file and unzip it.
    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('wget --http-user ' . $options['asda_user'] . ' --http-password ' . $options['asda_password'] . ' -O ' . $options['dumpfile'] . '.gz ' . $requestUrl . '/*.sql.gz')
      ->exec('gunzip ' . $options['dumpfile'] . '.gz');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
