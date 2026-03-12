<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to download and install dump files.
 */
class MoodleDumpCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/dump.yml';
    }

    /**
     * Import the production snapshot.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder|int
     *   Collection builder.
     *
     * @command moodle:install-dump
     *
     * @option dumpfile The dump file name.
     * @option myloader If set, MyLoader will be used to import the database.
     *
     * @aliases md-idump
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function moodleInstallDump(ConsoleIO $io, array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
        'myloader' => InputOption::VALUE_NONE,
    ])
    {
        $config = $this->getConfig();
        $myloader = $config->get('toolkit.clone.myloader');
        $database = $config->get('drupal.database');
        $databaseName = !empty($database['name']) && $database['name'] !== '${env.DRUPAL_DATABASE_NAME}' ? $database['name'] : '';
        $opts = ToolCommands::parseOptsYml();
        $isMyloader = $options['myloader'] || (isset($opts['mydumper']) && $opts['mydumper']);

        if ($isMyloader) {
            // The myloader should only be used with docker.
            if (!file_exists($myloader)) {
                $io->error('The import script was not found, to use MyLoader you must run on the corporate docker image.');
                return ResultData::EXITCODE_ERROR;
            }
            // When using myloader make sure dumpfile has tar extension.
            if (!str_ends_with($options['dumpfile'], '.tar') && str_ends_with($options['dumpfile'], '.gz')) {
                $options['dumpfile'] = str_replace('.gz', '.tar', $options['dumpfile']);
            }
        }

        $dumpFile = $this->tmpDirectory() . '/' . $options['dumpfile'];
        if (!file_exists($dumpFile)) {
            $io->error("'$dumpFile' file not found, use the command 'toolkit:download-dump'.");
            return ResultData::EXITCODE_ERROR;
        }

        $user = !empty($database['user']) && $database['user'] !== '${env.DRUPAL_DATABASE_USER}' ? $database['user'] : '';
        $pass = !empty($database['password']) && $database['password'] !== '${env.DRUPAL_DATABASE_PASS}' ? $database['password'] : '';
        $host = !empty($database['host']) && $database['host'] !== '${env.DRUPAL_DATABASE_HOST}' ? $database['host'] : '';

        $tasks = [];

        // Generate database config file.
        $dbConfigFile = $this->tmpDirectory() . '/.mycnf';
        if (!file_exists($dbConfigFile)) {
            $tasks[] = $this->taskWriteToFile($dbConfigFile)
                ->line("[client]")
                ->line("host=$host")
                ->line("user=$user")
                ->line("password=$pass");
        }

        // Recreate the database.
        $tasks[] = $this->taskExec("mysql --defaults-extra-file=$dbConfigFile -e 'DROP DATABASE IF EXISTS $databaseName'");
        $tasks[] = $this->taskExec("mysql --defaults-extra-file=$dbConfigFile -e 'CREATE DATABASE IF NOT EXISTS $databaseName'");

        if ($isMyloader) {
            $tasks[] = $this->taskExec($myloader)->arg($dumpFile);
        } else {
            $tasks[] = $this->taskImportDatabase($dumpFile);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Return the tmp folder path, folder is created if missing.
     *
     * @return string
     *   The tmp folder path.
     */
    private function tmpDirectory(): string
    {
        $tmpFolder = (string) $this->getConfig()->get('toolkit.tmp_folder');
        if (!file_exists($tmpFolder)) {
            if (!@mkdir($tmpFolder)) {
                $tmpFolder = sys_get_temp_dir();
            }
        }
        return $tmpFolder;
    }

    /**
     * Import given dump file, gunzip is used if dump ends with .gz.
     *
     * @param string $dump
     *   The path to the dump file.
     *
     * @return \Robo\Task\Base\ExecStack
     *   The file imported task.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function taskImportDatabase(string $dump)
    {
        $config = $this->getConfig()->get('drupal.database');
        $user = !empty($config['user']) && $config['user'] !== '${env.DRUPAL_DATABASE_USERNAME}' ? $config['user'] : '';
        $pass = !empty($config['password']) && $config['password'] !== '${env.DRUPAL_DATABASE_PASSWORD}' ? $config['password'] : '';
        $host = !empty($config['host']) && $config['host'] !== '${env.DRUPAL_DATABASE_HOST}' ? $config['host'] : '';
        $name = !empty($config['name']) && $config['name'] !== '${env.DRUPAL_DATABASE_NAME}' ? $config['name'] : '';
        $mysql = sprintf('mysql -u%s%s -h%s %s', $user, $pass ? ' -p' . $pass : '', $host, $name);
        if (str_ends_with($dump, '.gz')) {
            $command = "gunzip < $dump | $mysql";
        } else {
            $command = "$mysql < $dump";
        }

        return $this->taskExecStack()->stopOnFail()->silent(true)
            ->exec($command);
    }

}
