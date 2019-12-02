<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Consolidation\Config\Config;
use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Loader\YamlConfigLoader;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to clone a site for development and a production artifact.
 */
class CloneCommands extends AbstractCommands
{

    use TaskRunnerTasks\CollectionFactory\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../../config/commands/clone.yml';
    }

    /**
     * Run deployment sequence.
     *
     * This command will check for a file that holds the deployment sequence. If
     * it is available it will run the commands defined in the yaml file under the
     * selected key. If not we will run a standard set of deployment commands.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command toolkit:run-deploy
     *
     * @option sequence-file  The file that holds the deployment sequence.
     * @option sequence-key   The key under which the commands are defined.
     * @option config-file    The config file that triggers the config import.
     */
    public function runDeploy(array $options = [
        'sequence-file' => InputOption::VALUE_REQUIRED,
        'sequence-key' => InputOption::VALUE_REQUIRED,
        'config-file' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];

        $has_config = file_exists($options['config-file']);
        $has_sequence = file_exists($options['sequence-file']);

        if ($has_sequence) {
            $config = new Config();
            $loader = new YamlConfigLoader();
            $processor = new ConfigProcessor();
            $processor->extend($loader->load($options['sequence-file']));
            $config->import($processor->export());
            $sequence = $config->get($options['sequence-key']);

            if (!empty($sequence)) {
                $this->say('Running custom deploy sequence "' . $options['sequence-key'] . '" from sequence file "' . $options['sequence-file'] . '".');
                foreach ($sequence as $command) {
                    $tasks[] = $this->taskExec($command);
                }
                return $this->collectionBuilder()->addTaskList($tasks);
            } else {
                $this->say('Sequence key "' . $options['sequence-key'] . '" does not contain commands, running default set of deployment commands.');
            }
        } else {
            $this->say('Sequence file "' . $options['sequence-file'] . '" does not exist, running default set of deployment commands.');
        }

        // Default deployment sequence.
        $tasks[] = $this->taskExec('./vendor/bin/drush state:set system.maintenance_mode 1 --input-format=integer -y');
        $tasks[] = $this->taskExec('./vendor/bin/drush updatedb -y');
        if ($has_config) {
            $tasks[] = $this->taskExec('./vendor/bin/run toolkit:import-config');
        }
        $tasks[] = $this->taskExec('./vendor/bin/drush state:set system.maintenance_mode 0 --input-format=integer -y');
        $tasks[] = $this->taskExec('./vendor/bin/drush cache:rebuild');

        return $this->collectionBuilder()->addTaskList($tasks);
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
    ])
    {
        $tasks = [];

        if (!file_exists($options['dumpfile'])) {
            $this->say('"' . $options['dumpfile'] . '" file not found, use the command "toolkit:download-dump --dumpfile ' . $options['dumpfile'] . '".');

            return $this->collectionBuilder()->addTaskList($tasks);
        }

        // Unzip and dump database file.
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('./vendor/bin/drush sql-drop -y')
            ->exec('./vendor/bin/drush sql-create -y')
            ->exec('./vendor/bin/drush sqlc < ' . $options['dumpfile']);

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
    ])
    {
        $tasks = [];

        // Check credentials.
        if ($options['asda-user'] === '${env.ASDA_USER}' || $options['asda-password'] === '${env.ASDA_PASSWORD}') {
            $this->say('ASDA credentials not found, set them as the following environment variables: ASDA_USER, ASDA_PASSWORD.');

            return $this->collectionBuilder()->addTaskList($tasks);
        }

        // Download the file.
        $this->downloadChecksumFile($options);
        $fileContent = file_get_contents('latest.sh1');
        $filename = trim(explode('  ', $fileContent)[1]);

        // Download the file.
        $tasks[] = $this->taskExec('wget')
            ->option('--http-user', $options['asda-user'])
            ->option('--http-password', $options['asda-password'])
            ->option('-O', $options['dumpfile'] . '.gz')
            ->option('-nH', $options['asda-url'] . '/' . $filename)
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
    ])
    {
        $tmpDir = $this->getConfig()->get("toolkit.tmp_folder");

        // Create temp folder to prepare dist build in.
        $tasks[] = $this->taskFilesystemStack()
            ->mkdir($tmpDir)
            ->run();

        $this->taskExec('wget')
            ->option('--http-user', $options['asda-user'])
            ->option('--http-password', $options['asda-password'])
            ->option('-O', 'latest.sh1')
            ->option('-nH', $options['asda-url'] . '/latest.sh1')
            ->option('-A', '.sh1')
            ->option('-P', './')
            ->run();
    }
}
