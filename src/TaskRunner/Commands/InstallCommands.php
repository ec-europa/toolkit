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
class InstallCommands extends AbstractCommands implements FilesystemAwareInterface {
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
   * @command toolkit:install-clone
   */
  public function clone() {
    // Create folder if non-existent.
    if (!is_file('./.tmp/dump.sql')) {
      // Get updated dump if the case.
      $this->databaseDownload();
    }

    // Unzip and dump database file.
    $this->taskExecStack()
      ->stopOnFail()
      ->exec('gunzip ./.tmp/dump.sql.gz')
      ->exec('vendor/bin/drush --uri=web sqlc < ./.tmp/dump.sql')
      ->exec('vendor/bin/drush --uri=web cr')
      ->exec('vendor/bin/drush --uri=web cst')
      ->exec('vendor/bin/drush --uri=web cim -y')
      ->run();

    $this->disableDrupalCache();
  }

  /**
   * Install clean website.
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
   * @command toolkit:install-clean
   */
  public function clean() {
    $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/run toolkit:build-dev')
      ->exec('./vendor/bin/run drupal:site-install')
      ->run();

    $this->disableDrupalCache();
  }

  /**
   * Download production snapshot.
   *
   * In order to make use of this functionality you must add your
   * ASDA credentials to your environment like. If the credentials
   * are not there you will be prompted to insert them.
   *
   * @command toolkit:database-download
   */
  private function databaseDownload() {
    // Create folder if non-existent.
    if (!is_dir('.tmp')) {
      $this->taskExec('mkdir -p .tmp')->run();
    }

    // Check credentials.
    if (getenv('ASDA_USER')) {
      $toolkitAsdaUser = getenv('ASDA_USER');
      $toolkitAsdaPass = getenv('ASDA_PASS');
    }
    else {
      $this->say("The credentials for access ASDA are not found in your env.");
      $toolkitAsdaUser = $this->ask("Please insert your user name!");
      $toolkitAsdaUser = $this->ask("Please insert your password!");
    }

    $client = new Client();
    $requestUrl = self::ASDA_URL . $this->config->get('project.id') . '/';
    $requestOptions = [
      'auth' => [
        $toolkitAsdaUser,
        $toolkitAsdaPass,
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
   * Disable agregation and clear cache.
   *
   * @command toolkit:disable-drupal-cache
   */
  private function disableDrupalCache() {
    $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/drush -y config-set system.performance css.preprocess 0')
      ->exec('./vendor/bin/drush -y config-set system.performance js.preprocess 0')
      ->exec('./vendor/bin/drush cr')
      ->run();
  }

}
