<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Consolidation\Config\Config;
use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Loader\YamlConfigLoader;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to download and install dump files.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class DumpCommands extends AbstractCommands
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
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder|int
     *   Collection builder.
     *
     * @command toolkit:install-dump
     *
     * @option dumpfile The dump file name.
     */
    public function toolkitInstallDump(array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        $tmp_folder = $this->tmpDirectory();
        if (!file_exists("$tmp_folder/{$options['dumpfile']}")) {
            $this->say("'$tmp_folder/{$options['dumpfile']}' file not found, use the command 'toolkit:download-dump'.");
            return 1;
        }

        // Unzip and dump database file.
        $drush_bin = $this->getBin('drush');
        $tasks[] = $this->taskExec($drush_bin)->arg('sql-drop')->rawArg('-y');
        $tasks[] = $this->taskExec($drush_bin)->arg('sql-create')->rawArg('-y');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->silent(true)
            ->exec(sprintf(
                "gunzip < %s | mysql -u%s%s -h%s %s",
                "$tmp_folder/{$options['dumpfile']}",
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
     * @codingStandardsIgnoreStart Generic.Commenting.DocComment.TagsNotGrouped
     *
     * Configuration for ASDA in NEXTCLOUD.
     * - Environment variables: NEXTCLOUD_USER, NEXTCLOUD_PASS (EU Login).
     * - Runner variables:
     *
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
     *
     * @code
     * toolkit:
     *   clone:
     *     asda_type: 'default'
     *     asda_url: 'webgate.ec.europa.eu/fpfis/files-for/automate_dumps/${toolkit.project_id}'
     * @endcode
     *
     * @codingStandardsIgnoreEnd
     *
     * @command toolkit:download-dump
     *
     * @option is-admin For nextcloud admin user.
     * @option yes      Skip the question to download newer dump.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitDownloadDump(array $options = [
        'is-admin' => InputOption::VALUE_NONE,
        'yes' => InputOption::VALUE_NONE,
    ])
    {
        $tasks = [];
        $config = $this->getConfig();
        $project_id = $config->get('toolkit.project_id');
        $asda_type = $config->get('toolkit.clone.asda_type', 'nextcloud');
        $asda_services = (array) $config->get('toolkit.clone.asda_services', 'mysql');
        $vendor = $config->get('toolkit.clone.asda_vendor');
        $source = $config->get('toolkit.clone.asda_source');
        $tmp_folder = $this->tmpDirectory();
        $is_admin = !($options['is-admin'] === InputOption::VALUE_NONE) || $config->get('toolkit.clone.nextcloud_admin');
        if (Toolkit::isCiCd()) {
            $is_admin = true;
        }

        $this->say("ASDA type is: $asda_type" . ($asda_type === 'default' ? ' (The legacy ASDA will be dropped on 1 June)' : ''));
        $this->say('ASDA services: ' . implode(', ', $asda_services));

        if ($asda_type === 'default') {
            $user = Toolkit::getAsdaUser();
            $password = Toolkit::getAsdaPass();
            // Workaround, EWPP projects uses the ASDA_URL.
            $url = getenv('ASDA_URL') ?: $config->get('toolkit.clone.asda_url');
        } elseif ($asda_type === 'nextcloud') {
            $user = Toolkit::getNextcloudUser();
            $password = Toolkit::getNextcloudPass();
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
            if (empty($password = $this->askHidden('Please insert your password:'))) {
                $this->writeln('<error>The password cannot be empty!</error>');
                return $this->collectionBuilder()->addTaskList($tasks);
            }
        }

        $url = str_replace(['http://', 'https://'], '', $url);
        $download_link = "https://$user:$password@$url";
        if ($asda_type === 'nextcloud') {
            if ($is_admin) {
                $download_link .= "/$user/forDevelopment/$vendor/$project_id-$source";
            } else {
                $download_link .= "/$user/$project_id-$source";
            }
        }

        foreach ($asda_services as $service) {
            $this->say("Checking service '$service'");
            // Check if the dump is already downloaded.
            if (file_exists("$tmp_folder/$service.gz")) {
                $this->say("File found '$tmp_folder/$service.gz', checking server for newer dump");
                if ($this->checkForNewerDump($download_link, $service)) {
                    $question = "A newer dump was found, would you like to download?";
                    if (!Toolkit::isCiCd() && $options['yes'] === InputOption::VALUE_NONE) {
                        $answer = $this->confirm($question);
                    } else {
                        $this->say($question . ' (y/n) Y');
                        $answer = true;
                    }
                    if ($answer) {
                        $this->say('Starting download');
                        if ($asda_type === 'nextcloud') {
                            $tasks = array_merge($tasks, $this->asdaProcessFile("$download_link/$service", $service));
                        } else {
                            $tasks = $this->asdaProcessFile($download_link, $service);
                        }
                    } else {
                        $this->say('Skipping download');
                    }
                } else {
                    $this->say('Local dump is up-to-date');
                }
            } else {
                $this->say('Starting download');
                if ($asda_type === 'nextcloud') {
                    $tasks = array_merge($tasks, $this->asdaProcessFile("$download_link/$service", $service));
                } else {
                    $tasks = $this->asdaProcessFile($download_link, $service);
                }
            }
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Check if a newer dump exists on the server.
     *
     * @param string $link
     *   The link to the folder.
     * @param string $service
     *   The service to use.
     *
     * @return bool
     *   Return true if sha1 from local is different from the server,
     *   False is case of error or no local file exists.
     */
    private function checkForNewerDump(string $link, string $service)
    {
        $config = $this->getConfig();
        $tmp_folder = $this->tmpDirectory();
        if (!file_exists("$tmp_folder/$service.gz")) {
            return false;
        }
        if ($config->get('toolkit.clone.asda_type') === 'nextcloud') {
            $link .= "/$service";
        }
        // Download the .sha file.
        $this->generateAsdaWgetInputFile("$link/latest.sh1", "$tmp_folder/$service.txt", true);
        $this->wgetDownloadFile("$tmp_folder/$service.txt", "$tmp_folder/$service-latest.sh1", '.sh1', true)
            ->run();
        if (!file_exists("$tmp_folder/$service-latest.sh1")) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return false;
        }
        $latest = file_get_contents("$tmp_folder/$service-latest.sh1");
        if (empty($latest)) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return false;
        }
        $sha1 = trim(explode('  ', $latest)[0]);

        // Remove temporary files.
        $this->taskExec('rm')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->arg("$tmp_folder/$service-latest.sh1")
            ->arg("$tmp_folder/$service.txt")
            ->run();

        // Compare with the local dump.
        if ($sha1 !== sha1_file("$tmp_folder/$service.gz")) {
            return true;
        }
        return false;
    }

    /**
     * Helper to download and process a ASDA file.
     *
     * @param string $link
     *   The link to the folder.
     * @param string $service
     *   The service to use.
     *
     * @return array
     *   The tasks to execute.
     */
    private function asdaProcessFile(string $link, string $service)
    {
        $tasks = [];
        $tmp_folder = $this->tmpDirectory();

        // Download the .sha file.
        $this->generateAsdaWgetInputFile("$link/latest.sh1", "$tmp_folder/$service.txt", true);
        $this->wgetDownloadFile("$tmp_folder/$service.txt", "$tmp_folder/$service-latest.sh1", '.sh1', true)
            ->run();
        if (!file_exists("$tmp_folder/$service-latest.sh1")) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return $tasks;
        }
        $latest = file_get_contents("$tmp_folder/$service-latest.sh1");
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
        $this->generateAsdaWgetInputFile("$link/$filename", "$tmp_folder/$service.txt", true);
        $tasks[] = $this
            ->wgetDownloadFile("$tmp_folder/$service.txt", "$tmp_folder/$service.gz", '.sql.gz,.tar.gz');

        // Remove temporary files.
        $tasks[] = $this->taskExec('rm')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->arg("$tmp_folder/$service-latest.sh1")
            ->arg("$tmp_folder/$service.txt");

        return $tasks;
    }

    /**
     * Create file containing a url for usage in wget --input-file argument.
     *
     * @param string $url
     *   Url to fill in the temp file.
     * @param string $tmp
     *   The temporary filename.
     * @param bool $silent
     *   Whether show or not output from task.
     */
    private function generateAsdaWgetInputFile(string $url, string $tmp, bool $silent = false)
    {
        $task = $this->taskFilesystemStack()
            ->taskWriteToFile($tmp)
            ->line($url);
        if ($silent) {
            $task->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG);
        }
        $task->run();
    }

    /**
     * Download the file present in the tmp file.
     *
     * @param string $tmp
     *   The temporary filename.
     * @param string $destination
     *   The destination filename.
     * @param string|null $accept
     *   A comma-separated list of accepted extensions.
     * @param bool $silent
     *   Whether show or not output from task.
     *
     * @return \Robo\Collection\CollectionBuilder|\Robo\Task\Base\Exec
     */
    private function wgetDownloadFile(string $tmp, string $destination, string $accept = null, bool $silent = false)
    {
        $task = $this->taskExec('wget')
            ->option('-i', $tmp)
            ->option('-O', $destination)
            ->option('-A', $accept)
            ->option('-P', './')
            ->printMetadata(false);
        if ($silent) {
            $task->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG);
        }
        return $task;
    }

    /**
     * Return the tmp folder path, folder is created if missing.
     *
     * @return string
     *   The tmp folder path.
     */
    private function tmpDirectory(): string
    {
        $tmp_folder = (string) $this->getConfig()->get('toolkit.tmp_folder');
        if (!file_exists($tmp_folder)) {
            if (!@mkdir($tmp_folder)) {
                $tmp_folder = sys_get_temp_dir();
            }
        }
        return $tmp_folder;
    }

}
