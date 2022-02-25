<?php

declare(strict_types=1);

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
    public const TEMP_INPUTFILE = 'temporary_inputfile.txt';

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
     * Configuration for ASDA in NEXTCLOUD.
     * - Environment variables: NEXTCLOUD_USER, NEXTCLOUD_PASS (EU Login).
     * - Runner variables:
     * @code
     * toolkit:
     *   clone:
     *     asda_type: 'nextcloud'
     *     nextcloud_url: 'files.fpfis.tech.ec.europa.eu/remote.php/dav/files'
     * @endcode
     *
     * Configuration for ASDA default.
     * - Environment variables: ASDA_USER, ASDA_PASSWORD.
     * - Runner variables:
     * @code
     * toolkit:
     *   clone:
     *     asda_type: 'default'
     *     asda_url: 'webgate.ec.europa.eu/fpfis/files-for/automate_dumps/${toolkit.project_id}'
     * @endcode
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @command toolkit:download-dump
     *
     * @option asda-url      Overrides `${toolkit.clone.asda_url}`
     * @option asda-user     Overrides `ASDA_USER` or `NEXTCLOUD_USER`
     * @option asda-password Overrides `ASDA_PASSWORD` or `NEXTCLOUD_PASS`
     * @option dumpfile      Overrides `${toolkit.clone.dumpfile}`
     *
     * @return \Robo\Collection\CollectionBuilder|void
     *   Collection builder.
     */
    public function downloadDump(array $options = [
        'asda-url' => InputOption::VALUE_OPTIONAL,
        'asda-user' => InputOption::VALUE_OPTIONAL,
        'asda-password' => InputOption::VALUE_OPTIONAL,
        'dumpfile' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $tasks = [];
        $config = $this->getConfig();
        $project_id = $config->get('toolkit.project_id');
        $asda_type = $config->get('toolkit.clone.asda_type');
        $dump_file = $options['dumpfile'] ?: $config->get('toolkit.clone.dumpfile');
        $user = $options['asda-user'] ?: false;
        $password = $options['asda-password'] ?: false;
        $this->say("ASDA type is: $asda_type");
        if ($asda_type === 'default') {
            if (!$user) {
                $user = getenv('ASDA_USER') && getenv('ASDA_USER') !== '${env.ASDA_USER}' ? getenv('ASDA_USER') : '';
            }
            if (!$password) {
                $password = getenv('ASDA_PASSWORD') && getenv('ASDA_PASSWORD') !== '${env.ASDA_PASSWORD}' ? getenv('ASDA_PASSWORD') : '';
            }
            $url = $options['asda-url'] ?: $config->get('toolkit.clone.asda_url');
        } elseif ($asda_type === 'nextcloud') {
            if (!$user) {
                $user = getenv('NEXTCLOUD_USER') && getenv('NEXTCLOUD_USER') !== '${env.NEXTCLOUD_USER}' ? getenv('NEXTCLOUD_USER') : '';
            }
            if (!$password) {
                $password = getenv('NEXTCLOUD_PASS') && getenv('NEXTCLOUD_PASS') !== '${env.NEXTCLOUD_PASS}' ? getenv('NEXTCLOUD_PASS') : '';
            }
            $url = $options['asda-url'] ?: $config->get('toolkit.clone.nextcloud_url');
        } else {
            $this->writeln('<error>Invalid value for variable ${toolkit.clone.asda_type}, use "default" or "nextcloud".</error>');
            return $this->collectionBuilder()->addTaskList($tasks);
        }

        if (empty($user)) {
            if (empty($user = $this->ask('Please insert your username?'))) {
                $this->writeln('<error>The username cannot be empty!</error>');
                return $this->collectionBuilder()->addTaskList($tasks);
            }
        }
        if (empty($password)) {
            if (empty($password = $this->ask('Please insert your password?'))) {
                $this->writeln('<error>The password cannot be empty!</error>');
                return $this->collectionBuilder()->addTaskList($tasks);
            }
        }

        $url = str_replace(['http://', 'https://'], '', $url);
        if ($asda_type === 'nextcloud') {
            $url = "$url/$user/forDevelopment/ec-europa/$project_id-reference/mysql";
        }
        $download_link = "https://$user:$password@$url";

        // Download the .sha file.
        $this->generateAsdaWgetInputFile($download_link . '/latest.sh1');
        $this->wgetDownloadFile('latest.sh1', '.sh1')->run();
        $latest = file_get_contents('latest.sh1');
        if (empty($latest)) {
            $this->writeln('<error>Could not fetch the file latest.sh1</error>');
            return $this->collectionBuilder()->addTaskList($tasks);
        }
        $filename = trim(explode('  ', $latest)[1]);

        // Display information about ASDA creation date.
        preg_match('/(\d{8})(?:-)?(\d{4})(\d{2})?/', $filename, $matches);
        $date = !empty($matches) ? date_parse_from_format('YmdHis', $matches[1] . $matches[2] . ($matches[3] ?? '00')) : [];
        if (!empty($date) &&
            is_integer($date['hour']) &&
            is_integer($date['minute']) &&
            is_integer($date['month']) &&
            is_integer($date['day']) &&
            is_integer($date['year'])
        ) {
            $timestamp = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
            $output = sprintf('%d %s %d at %s:%s', $date['day'], date('M', $timestamp), $date['year'], $date['hour'], $date['minute']);
        } else {
            $output = $filename;
        }
        $output = "ASDA DATE: $output";
        $separator = str_repeat('=', strlen($output));
        $this->writeln("\n<info>$output\n$separator</info>\n");

        // Download the .sql file.
        $this->generateAsdaWgetInputFile($download_link . '/' . $filename);
        $tasks[] = $this->wgetDownloadFile($dump_file . '.gz', '.sql.gz');

        // Unzip the file.
        $tasks[] = $this->taskExec('gunzip')
            ->arg($dump_file . '.gz')
            ->option('-f');

        // Remove temporary files.
        $tasks[] = $this->taskExec('rm')
            ->arg('latest.sh1')
            ->arg(self::TEMP_INPUTFILE);

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Create file containing a url for usage in wget --input-file argument.
     *
     * @param string $url
     *   Url to fill in the temp file.
     */
    private function generateAsdaWgetInputFile($url)
    {
        $this->taskFilesystemStack()
            ->taskWriteToFile(self::TEMP_INPUTFILE)
            ->line($url)
            ->run();
    }

    /**
     * Download the file present in the tmp file.
     *
     * @param $destination
     *   The destination filename.
     * @param $accept
     *   A comma-separated list of accepted extensions.
     *
     * @return \Robo\Collection\CollectionBuilder|\Robo\Task\Base\Exec
     */
    private function wgetDownloadFile($destination, $accept = null)
    {
        return $this->taskExec('wget')
            ->option('-i', self::TEMP_INPUTFILE)
            ->option('-O', $destination)
            ->option('-A', $accept)
            ->option('-P', './');
    }
}
