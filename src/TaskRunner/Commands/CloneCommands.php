<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to clone a site for development and a production artifact.
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
   * It restores the database and imports the configuration.
   * - Verify if the dumpfile exists.
   * - Import configuration from sync into active storage.
   * - Execute cache-rebuild.
   *
   * @param array $options
   *   Command options.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   *
   * @command toolkit:install-dump
   *
   * @option dumpfile Drupal uri.
   */
  public function installDump(array $options = [
    'dumpfile' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];

    if (!file_exists($options['dumpfile'])) {
      $this->say('"' . $options['dumpfile'] . '" file not found, use the command "toolkit:download-dump --dumpfile ' . $options['dumpfile'] . '".');

      return $this->collectionBuilder()->addTaskList($tasks);
    }

    // Unzip and dump database file.
    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/drush sql-drop -y')
      ->exec('./vendor/bin/drush sqlc < ' . $options['dumpfile'])
      ->exec('./vendor/bin/drush updatedb -y')
      ->exec('./vendor/bin/drush cache:rebuild');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Download ASDA snapshot.
   *
   * In order to make use of this functionality you must add your
   * ASDA credentials to your environment like.
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
    'asda-url' => InputOption::VALUE_REQUIRED,
    'asda-user' => InputOption::VALUE_REQUIRED,
    'asda-password' => InputOption::VALUE_REQUIRED,
    'dumpfile' => InputOption::VALUE_REQUIRED,
    'project-id' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];

    // Check credentials.
    if ($options['asda-user'] === '${env.ASDA_USER}' || $options['asda-password'] === '${env.ASDA_PASSWORD}') {
      $this->say('ASDA credentials not found, set them as the following environment variables: ASDA_USER, ASDA_PASSWORD.');

      return $this->collectionBuilder()->addTaskList($tasks);
    }

    $requestUrl = $options['asda-url'] . '/' . $options['project-id'];

    // Download the file.
    $this->downloadChecksumFile($options);
    $fileContent = file_get_contents('latest.sh1');
    $filename = trim(explode('  ', $fileContent)[1]);

    // Download the file.
    $tasks[] = $this->taskExec('wget')
      ->option('--http-user', $options['asda-user'])
      ->option('--http-password', $options['asda-password'])
      ->option('-O', $options['dumpfile'] . '.gz')
      ->option('-nH', $requestUrl . '/' . $filename)
      ->option('-A', 'sql.gz')
      ->option('-P', './');

    // Unzip the file.
    $tasks[] = $this->taskExec('gunzip')
      ->arg($options['dumpfile'] . '.gz');

    // Remove checkum file.
    $tasks[] = $this->taskExec('rm')
      ->arg('latest.sh1');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Download Checksum file.
   *
   * Make use checksum file in order to detect the proper file
   * to download.
   *
   * @param array $options
   *   Command options.
   */
  private function downloadChecksumFile(array $options = [
    'asda-url' => InputOption::VALUE_REQUIRED,
    'asda-user' => InputOption::VALUE_REQUIRED,
    'asda-password' => InputOption::VALUE_REQUIRED,
    'dumpfile' => InputOption::VALUE_REQUIRED,
    'project-id' => InputOption::VALUE_REQUIRED,
  ]) {
    $tmpDir = $this->getConfig()->get("toolkit.tmp_folder");

    // Create temp folder to prepare dist build in.
    $tasks[] = $this->taskFilesystemStack()
      ->mkdir($tmpDir)
      ->run();

    $requestUrl = $options['asda-url'] . '/' . $options['project-id'];

    $this->taskExec('wget')
      ->option('--http-user', $options['asda-user'])
      ->option('--http-password', $options['asda-password'])
      ->option('-O', 'latest.sh1')
      ->option('-nH', $requestUrl . '/latest.sh1')
      ->option('-A', '.sh1')
      ->option('-P', './')
      ->run();

  }

}
