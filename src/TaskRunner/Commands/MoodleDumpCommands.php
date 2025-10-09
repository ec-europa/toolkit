<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to download and install dump files.
 */
class MoodleDumpCommands extends DumpCommands
{

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
     */
    public function toolkitInstallDump(ConsoleIO $io, array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED
    ])
    {
        $config = $this->getConfig();
        $databaseName = $config->get('moodle.db_name');
        $opts = ToolCommands::parseOptsYml();

        $dumpFile = $this->tmpDirectory() . '/' . $options['dumpfile'];
        if (!file_exists($dumpFile)) {
            $io->error("'$dumpFile' file not found, use the command 'toolkit:download-dump'.");
            return ResultData::EXITCODE_ERROR;
        }
        $tasks = [];

        // Recreate the database.
        //$tasks[] = $this->taskExec("mysql --defaults-extra-file=$mySqlConfigFile -e 'DROP DATABASE IF EXISTS $databaseName'");
        //$tasks[] = $this->taskExec("mysql --defaults-extra-file=$mySqlConfigFile -e 'CREATE DATABASE IF NOT EXISTS $databaseName'");

        $tasks[] = $this->taskImportDatabase($dumpFile);

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
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
        $user = !empty($config['user']) && $config['user'] !== '${env.MOODLE_DB_USER}' ? $config['user'] : '';
        $pass = !empty($config['password']) && $config['password'] !== '${env.MOODLE_DB_PASS}' ? $config['password'] : '';
        $host = !empty($config['host']) && $config['host'] !== '${env.MOODLE_DB_HOST}' ? $config['host'] : '';
        $name = !empty($config['name']) && $config['name'] !== '${env.MOODLE_DB_NAME}' ? $config['name'] : '';
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
