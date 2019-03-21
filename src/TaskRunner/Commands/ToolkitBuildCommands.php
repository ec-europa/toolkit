<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use GuzzleHttp\Client;

/**
 * Class ToolkitCommands.
 */
class ToolkitBuildCommands extends AbstractCommands implements FilesystemAwareInterface {
  use NuvoleWebTasks\Config\loadTasks;
  use TaskRunnerTasks\CollectionFactory\loadTasks;
  use TaskRunnerTraits\ConfigurationTokensTrait;
  use TaskRunnerTraits\FilesystemAwareTrait;
  const ASDA_URL = 'https://webgate.ec.europa.eu/fpfis/files-for/automate_dumps/';

  public $dumpFilename = '';

  /**
   * Install clone from production snapshot.
   *
   * This will download the database if none local then proceed to dump and sync
   * the configuration in the following order:
   * - Verify if .tmp/dump.sql or dump.sql exists, if not download it
   *   in .tmp/dump.sql
   * - Import dump.sql in the current installation
   * - Execute cache-rebuild
   * - Check current status of configuration
   * - Import configuration from datastore into activestore.
   *
   * @command toolkit:clone
   *
   * @aliases tc
   */
  public function toolkitClone() {
    // Create folder if non-existent.
    if (!is_file('./.tmp/dump.sql') || !is_file('./dump.sql')) {
      // Get updated dump if the case.
      $this->toolkitDatabaseDownload();
    }

    // Unzip and dump database file.
    $this->taskExecStack()
      ->stopOnFail()
      ->exec('gunzip .tmp/dump.sql.gz')
      ->exec('vendor/bin/drush --uri=web sqlc < .tmp/dump.sql')
      ->exec('vendor/bin/drush --uri=web cr')
      ->exec('vendor/bin/drush --uri=web cst')
      ->exec('vendor/bin/drush --uri=web cim -y')
      ->run();
  }

  /**
   * Download production snapshot.
   *
   * @command toolkit:database-download
   *
   * @aliases tdd
   */
  public function toolkitDatabaseDownload() {
    // Create folder if non-existent.
    if (!is_dir('.tmp')) {
      $this->taskExec('mkdir -p .tmp')->run();
    }

    $client = new Client();
    $requestUrl = self::ASDA_URL . $this->config->get('project.id') . '/';
    $requestOptions = [
      'auth' => [
        $this->config->get('asda.user'),
        $this->config->get('asda.password'),
      ],
    ];

    // Get current filename.
    $response = $client->request('GET', $requestUrl, $requestOptions);
    $body = (string) $response->getBody();

    foreach (preg_split("/((\r?\n)|(\r\n?))/", trim(strip_tags($body))) as $key => $line) {
      $this->dumpFilename = (strpos($line, '.sql.gz')) ? trim($line) : '';
    }

    // Check if this files is already downloaded.
    if (!is_file('.tmp/' . $this->dumpFilename)) {
      // Download database.
      if ($this->dumpFilename) {
        $requestOptions += ['sink' => '.tmp/dump.sql.gz'];
        $client->request('GET', $requestUrl . $this->dumpFilename, $requestOptions);
      }
    }
  }

  /**
   * Build the distribution package.
   *
   * This will create the distribution package intended to be deployed.
   * The folder structure should match the following:
   * - /dist
   * - /dist/composer.json
   * - /dist/composer.lock
   * - /dist/web
   * - /dist/vendor
   * - /dist/config.
   *
   * @command toolkit:build-dist
   *
   * @aliases tbd
   */
  public function toolkitBuildDist() {
    // Reset dist folder and copy required files.
    $this
      ->taskFilesystemStack()
      ->remove('./dist')
      ->mkdir('./dist')
      ->copy('composer.json', './dist/composer.json')
      ->copy('composer.lock', './dist/composer.lock')
      ->run();

    // Copy configuration and install packages.
    $this
      ->taskCopyDir(['config' => 'dist/config'])
      ->exec('composer install --no-dev --optimize-autoloader --working-dir=dist')
      ->copy('dist/${drupal.root}/sites/default/default.settings.php', 'dist/${drupal.root}/sites/default/settings.php')
      ->run();
  }

}
