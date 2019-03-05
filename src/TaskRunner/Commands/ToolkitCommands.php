<?php

namespace Eceuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Consolidation\AnnotatedCommand\CommandData;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;

/**
 * Class ToolkitCommands
 *
 * @package Eceuropa\Toolkit\TaskRunner\Commands
 */
class ToolkitCommands extends AbstractCommands implements FilesystemAwareInterface
{
    const ASDA_URL = 'https://webgate.ec.europa.eu/fpfis/files-for/automate_dumps/';

    public $dumpFilename = '';

    use TaskRunnerTraits\ConfigurationTokensTrait;
    use TaskRunnerTraits\FilesystemAwareTrait;
    use TaskRunnerTasks\CollectionFactory\loadTasks;
    use NuvoleWebTasks\Config\loadTasks;

    /**
     * Install clone from production snapshot.
     *
     * @command toolkit:clone
     *
     * @aliases tc
     */
    public function toolkitClone()
    {
      // Get updated dump if the case.
      $this->toolkitDatabaseDownload();

      // Unzip and dump database file.
      $this->taskExecStack()
        ->stopOnFail()
        ->exec('gunzip .tmp/dump.sql.gz')
        ->exec('vendor/bin/drush --uri=web sqlc < .tmp/dump.sql')
        ->run();
    }

    /**
     * Download production snapshot.
     *
     * @command toolkit:database-download
     *
     * @aliases tdd
     */
    public function toolkitDatabaseDownload()
    {

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
        ]
      ];

      // Get current filename.
      $response = $client->request('GET', $requestUrl, $requestOptions);
      $body = (string) $response->getBody();

      foreach(preg_split("/((\r?\n)|(\r\n?))/", trim(strip_tags($body))) as $key => $line){
        $hashFilename = (strpos($line, '.sh')) ? trim($line) : 'latest.sh1';
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
     * Run PHP code review
     * @command toolkit:test-phpcs
     *
     * @aliases ttp
     */
    public function toolkitTestPhpcs()
    {
      return $this->taskExec("./vendor/bin/grumphp run")->run();
    }

}
