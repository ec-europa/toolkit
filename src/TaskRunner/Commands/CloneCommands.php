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
     * Path to file that hold the input information.
     */
    const TEMP_INPUTFILE = 'temporary_inputfile.txt';

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
                $sequence = isset($sequence['default']) ? $sequence['default'] : $sequence;
                $this->say('Running custom deploy sequence "' . $options['sequence-key'] . '" from sequence file "' . $options['sequence-file'] . '".');
                foreach ($sequence as $command) {
                    // Only execute strings. Opts.yml also supports append and
                    // default array to append or override the default commands.
                    // @see: https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/NE+Pipelines#NEPipelines-DeploymentOverrides
                    // @see: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-23137
                    if (is_string($command)) {
                        $tasks[] = $this->taskExec($command);
                    }
                }
                return $this->collectionBuilder()->addTaskList($tasks);
            } else {
                $this->say('Sequence key "' . $options['sequence-key'] . '" does not contain commands, running default set of deployment commands.');
            }
        } else {
            $this->say('Sequence file "' . $options['sequence-file'] . '" does not exist, running default set of deployment commands.');
        }

        // Default deployment sequence.
        $drush_dir = $this->getBin('drush');
        $tasks[] = $this->taskExec($drush_dir . ' state:set system.maintenance_mode 1 --input-format=integer -y');
        $tasks[] = $this->taskExec($drush_dir . ' updatedb -y');
        if ($has_config) {
            $tasks[] = $this->taskExec($this->getBin('run') . ' toolkit:import-config');
        }
        $tasks[] = $this->taskExec($drush_dir . ' state:set system.maintenance_mode 0 --input-format=integer -y');
        $tasks[] = $this->taskExec($drush_dir . ' cache:rebuild');

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
        $drush_bin = $this->getBin('drush');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($drush_bin . ' sql-drop -y')
            ->exec($drush_bin . ' sql-create -y')
            ->exec($drush_bin . ' sqlc < ' . $options['dumpfile']);

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Download ASDA snapshot.
     *
     * In order to make use of this functionality you must add your
     * ASDA credentials to your environment like.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
            // Check token
            if (!getenv('CI')) {
                $this->say('ASDA credentials not found, set them as the following environment variables: ASDA_USER, ASDA_PASSWORD.');
                return $this->collectionBuilder()->addTaskList($tasks);
            }
        }

        // Download the .sha file.
        $this->generateAsdaWgetInputFile('latest.sh1', $options);
        $this->downloadChecksumFile($options);
        $fileContent = file_get_contents('latest.sh1');
        $filename = trim(explode('  ', $fileContent)[1]);

        // Download the .sql file.
        $this->generateAsdaWgetInputFile($filename, $options);

        $tasks[] = $this->taskExec('wget')
            ->option('-O', $options['dumpfile'] . '.gz')
            ->option('-i', self::TEMP_INPUTFILE)
            ->option('-A', 'sql.gz')
            ->option('-P', './');

        // Unzip the file.
        $tasks[] = $this->taskExec('gunzip')
            ->arg($options['dumpfile'] . '.gz');

        // Remove temporary files.
        $tasks[] = $this->taskExec('rm')
            ->arg('latest.sh1')
            ->arg(self::TEMP_INPUTFILE);

        // Display information about ASDA creation date.
        $dumpData = substr(substr(file_get_contents('latest.sh1'), (strpos(file_get_contents('latest.sh1'), ' ')) + 2), 0, 15);
        $dumpDate = date_parse_from_format("Ymd-His", $dumpData);
        if (is_array($dumpDate) &&
            is_integer($dumpDate['hour']) &&
            is_integer($dumpDate['minute']) &&
            is_integer($dumpDate['second']) &&
            is_integer($dumpDate['month']) &&
            is_integer($dumpDate['day']) &&
            is_integer($dumpDate['year'])
        ) {
            $dumpTimestamp = mktime($dumpDate['hour'], $dumpDate['minute'], $dumpDate['second'], $dumpDate['month'], $dumpDate['day'], $dumpDate['year']);
            $dumpHrdate = 'ASDA DATE: ' . $dumpDate['day'] . ' ' . date('M', $dumpTimestamp) . ' ' . $dumpDate['year'] . ' at ' . $dumpDate['hour'] . ':' . $dumpDate['minute'];
            $this->io()->title($dumpHrdate);
        }

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
            ->option('-i', self::TEMP_INPUTFILE)
            ->option('-O', 'latest.sh1')
            ->option('-A', '.sh1')
            ->option('-P', './')
            ->run();
    }

    /**
     * Create file for usage in wget --input-file argument in the
     * downloadDump() function.
     *
     * @param string $filename
     *   Name of filename to append to url.
     *
     * @param array $options
     *   Command options.
     */
    private function generateAsdaWgetInputFile($filename, array $options = [
        'asda-url' => InputOption::VALUE_REQUIRED,
        'asda-user' => InputOption::VALUE_REQUIRED,
        'asda-password' => InputOption::VALUE_REQUIRED,
    ])
    {
        // Workaround to EWPP projects.
        $url = getenv('ASDA_URL') ?: $options['asda-url'];

        $disallowed = array('http://', 'https://');
        foreach ($disallowed as $d) {
            if (strpos($url, $d) === 0) {
                $url = str_replace($d, '', $options['asda-url']);
            }
        }

        $downloadLink = 'https://' . $options['asda-user'] . ':' . $options['asda-password'] . '@' . $url . '/' . $filename;

        $tasks[] = $this->taskFilesystemStack()
            ->taskWriteToFile(self::TEMP_INPUTFILE)
            ->line($downloadLink)
            ->run();
    }
}
