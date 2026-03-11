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
     *
     * @aliases md-idump
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function moodleInstallDump(ConsoleIO $io, array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig()->get('drupal.database');
        $databaseName = !empty($config['name']) && $config['name'] !== '${env.DRUPAL_DATABASE_NAME}' ? $config['name'] : '';

        $dumpFile = $this->tmpDirectory() . '/' . $options['dumpfile'];
        if (!file_exists($dumpFile)) {
            $io->error("'$dumpFile' file not found, use the command 'toolkit:download-dump'.");
            return ResultData::EXITCODE_ERROR;
        }

        $user = !empty($config['user']) && $config['user'] !== '${env.DRUPAL_DATABASE_USER}' ? $config['user'] : '';
        $pass = !empty($config['password']) && $config['password'] !== '${env.DRUPAL_DATABASE_PASS}' ? $config['password'] : '';
        $host = !empty($config['host']) && $config['host'] !== '${env.DRUPAL_DATABASE_HOST}' ? $config['host'] : '';

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

        $tasks[] = $this->taskImportDatabase($dumpFile, $dbConfigFile);

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
     * @param string $dbConfigFile
     *   The path to the database config file.
     *
     * @return \Robo\Task\Base\ExecStack
     *   The file imported task.
     */
    private function taskImportDatabase(string $dump, string $dbConfigFile)
    {
        $databaseName = $this->getConfig()->get('drupal.database.name');
        $command = "zcat $dump | mysql --defaults-extra-file=$dbConfigFile $databaseName";

        return $this->taskExecStack()->stopOnFail()->silent(true)
            ->exec($command);
    }

}
