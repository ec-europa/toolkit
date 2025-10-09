<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MoodleInstallCommands.
 */
class MoodleInstallCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/install.yml';
    }

    /**
     * Install a moodle website using database dump.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:install-clone
     *
     * @option dumpfile The dump file name.
     *
     * @aliases md-iclone
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The cloned website task.
     */
    public function moodleInstallClone(array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        $config = $this->getConfig();
        $dataRoot = $config->get('moodle.data_root');
        $databaseName = $config->get('moodle.db_name');
        $mySqlConfigFile = '.mycnf';
        $tmpFolder = (string) $this->getConfig()->get('toolkit.tmp_folder');
        $dumpFile = $options['dumpfile'];

        // Setup the site.
        $runnerBin = $this->getBin('run');

        $tasks[] = $this->taskExec($runnerBin)
            ->arg('moodle:install-dump')
            ->option('dumpfile', $options['dumpfile'], '=');

        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            //->exec("$runnerBin toolkit:download-dump")
            //->exec("$runnerBin moodle:install-dump")
            ->exec("$runnerBin moodle:clear-cache");

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }




//  db:export:
//    - { task: exec, command: 'mysqldump --defaults-extra-file=${moodle.db_cnf} ${env.MOODLE_DB_NAME} | gzip > ${toolkit.tmp_folder}/${toolkit.clone.dumpfile}' }
//
//  db:import:
//    - { task: exec, command: '' }
//
//  db:config-setup:
//    - task: exec
//      command: |
//        if [ ! -f "${moodle.db_cnf}" ]; then
//          echo "[client]\nhost=${env.MOODLE_DB_HOST}\nuser=${env.MOODLE_DB_USER}" > ${moodle.db_cnf};
//          if [ ! -z "${env.MOODLE_DB_PASS}" ]; then
//            echo "\npassword=${env.MOODLE_DB_PASS}" >> ${moodle.db_cnf};
//          fi
//        fi

}
