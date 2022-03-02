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
                $sequence = $sequence['default'] ?? $sequence;
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
     * Import the production snapshot.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command toolkit:install-dump
     *
     * @option dumpfile The dump file name.
     */
    public function installDump(array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];

        if (!file_exists($options['dumpfile'])) {
            if (!getenv('CI')) {
                $this->say('"' . $options['dumpfile'] . '" file not found, use the command "toolkit:download-dump".');
                return $this->collectionBuilder()->addTaskList($tasks);
            }
        }

        // Unzip and dump database file.
        $drush_bin = $this->getBin('drush');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($drush_bin . ' sql-drop -y')
            ->exec($drush_bin . ' sql-create -y');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->silent(true)
            ->exec(sprintf(
                "gunzip < %s | mysql -u%s%s -h%s %s",
                $options['dumpfile'],
                getenv('DRUPAL_DATABASE_USERNAME'),
                getenv('DRUPAL_DATABASE_PASSWORD') ? ' -p' . getenv('DRUPAL_DATABASE_PASSWORD') : '',
                getenv('DRUPAL_DATABASE_HOST'),
                getenv('DRUPAL_DATABASE_NAME'),
            ));
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
     *     asda_services:
     *       - mysql
     *       - solr
     *       - virtuoso
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
     * @command toolkit:download-dump
     *
     * @return \Robo\Collection\CollectionBuilder|void
     *   Collection builder.
     */
    public function downloadDump()
    {
        $tasks = [];
        $config = $this->getConfig();
        $project_id = $config->get('toolkit.project_id');
        $asda_type = $config->get('toolkit.clone.asda_type', 'default');
        $asda_services = (array) $config->get('toolkit.clone.asda_services', 'mysql');

        $this->say("ASDA type is: $asda_type");
        $this->say('ASDA services: ' . implode(', ', $asda_services));
        if ($asda_type === 'default') {
            $user = getenv('ASDA_USER') && getenv('ASDA_USER') !== '${env.ASDA_USER}' ? getenv('ASDA_USER') : '';
            $password = getenv('ASDA_PASSWORD') && getenv('ASDA_PASSWORD') !== '${env.ASDA_PASSWORD}' ? getenv('ASDA_PASSWORD') : '';
            $url = $config->get('toolkit.clone.asda_url');
        } elseif ($asda_type === 'nextcloud') {
            $user = getenv('NEXTCLOUD_USER') && getenv('NEXTCLOUD_USER') !== '${env.NEXTCLOUD_USER}' ? getenv('NEXTCLOUD_USER') : '';
            $password = getenv('NEXTCLOUD_PASS') && getenv('NEXTCLOUD_PASS') !== '${env.NEXTCLOUD_PASS}' ? getenv('NEXTCLOUD_PASS') : '';
            $url = $config->get('toolkit.clone.nextcloud_url');
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
        $download_link = "https://$user:$password@$url";

        if ($asda_type === 'nextcloud') {
            $download_link .= "/$user/forDevelopment/ec-europa/$project_id-reference/";
            foreach ($asda_services as $service) {
                $tasks = array_merge($tasks, $this->asdaProcessFile($download_link . $service, $service));
            }
        } else {
            $tasks = $this->asdaProcessFile($download_link, 'mysql');
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Helper to download and process a ASDA file.
     *
     * @param $link
     *   The link to the folder.
     * @param $service
     *   The service to use.
     *
     * @return array
     *   The tasks to execute.
     */
    private function asdaProcessFile($link, $service)
    {
        $tasks = [];
        // Download the .sha file.
        $this->generateAsdaWgetInputFile("$link/latest.sh1", "$service.txt");
        $this->wgetDownloadFile("$service.txt", "$service-latest.sh1", '.sh1')->run();
        $latest = file_get_contents("$service-latest.sh1");
        if (empty($latest)) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return $tasks;
        }
        $filename = trim(explode('  ', $latest)[1]);

        // Display information about ASDA creation date.
        preg_match('/(\d{8})(?:-)?(\d{4})(\d{2})?/', $filename, $matches);
        $date = !empty($matches) ? date_parse_from_format('YmdHis', $matches[1] . $matches[2] . ($matches[3] ?? '00')) : [];
        if (
            !empty($date) &&
            is_integer($date['hour']) &&
            is_integer($date['minute']) &&
            is_integer($date['month']) &&
            is_integer($date['day']) &&
            is_integer($date['year'])
        ) {
            $timestamp = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
            $output = sprintf('%02d %s %d at %02d:%02d', $date['day'], date('M', $timestamp), $date['year'], $date['hour'], $date['minute']);
        } else {
            $output = $filename;
        }
        $output = strtoupper($service) . " DATE: $output";
        $separator = str_repeat('=', strlen($output));
        $this->writeln("\n<info>$output\n$separator</info>\n");

        // Download the file.
        $this->generateAsdaWgetInputFile("$link/$filename", "$service.txt");
        $tasks[] = $this->wgetDownloadFile("$service.txt", "$service.gz", '.sql.gz,.tar.gz');

        // Remove temporary files.
        $tasks[] = $this->taskExec('rm')
            ->arg("$service-latest.sh1")
            ->arg("$service.txt");

        return $tasks;
    }

    /**
     * Create file containing a url for usage in wget --input-file argument.
     *
     * @param string $url
     *   Url to fill in the temp file.
     * @param string $tmp
     *   The temporary filename.
     */
    private function generateAsdaWgetInputFile($url, $tmp)
    {
        $this->taskFilesystemStack()
            ->taskWriteToFile($tmp)
            ->line($url)
            ->run();
    }

    /**
     * Download the file present in the tmp file.
     *
     * @param $tmp
     *   The temporary filename.
     * @param $destination
     *   The destination filename.
     * @param null $accept
     *   A comma-separated list of accepted extensions.
     *
     * @return \Robo\Collection\CollectionBuilder|\Robo\Task\Base\Exec
     */
    private function wgetDownloadFile($tmp, $destination, $accept = null)
    {
        return $this->taskExec('wget')
            ->option('-i', $tmp)
            ->option('-O', $destination)
            ->option('-A', $accept)
            ->option('-P', './');
    }
}
