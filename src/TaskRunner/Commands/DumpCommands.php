<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to download and install dump files.
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
     * @option myloader If set, MyLoader will be used to import the database.
     *
     * @aliases tk-idump
     */
    public function toolkitInstallDump(ConsoleIO $io, array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
        'myloader' => InputOption::VALUE_NONE,
    ])
    {
        $config = $this->getConfig();
        $myloader = $config->get('toolkit.clone.myloader');
        $opts = ToolCommands::parseOptsYml();
        $isMyloader = $options['myloader'] || (isset($opts['mydumper']) && $opts['mydumper']);

        if ($isMyloader) {
            // The myloader should only be used with docker.
            if (!file_exists($myloader)) {
                $io->error('The import script was not found, to use MyLoader you must run on the corporate docker image.');
                return ResultData::EXITCODE_ERROR;
            }
            if (!str_ends_with($options['dumpfile'], '.tar')) {
                $io->error('To use MyLoader the dumpfile must be a .tar file.');
                return ResultData::EXITCODE_ERROR;
            }
        }
        $dumpFile = $this->tmpDirectory() . '/' . $options['dumpfile'];
        if (!file_exists($dumpFile)) {
            $io->error("'$dumpFile' file not found, use the command 'toolkit:download-dump'.");
            return ResultData::EXITCODE_ERROR;
        }
        $tasks = [];

        $drushBin = $this->getBin('drush');
        // Recreate the database.
        $tasks[] = $this->taskExec($drushBin)->arg('sql-drop')->option('-y');
        $tasks[] = $this->taskExec($drushBin)->arg('sql-create')->option('-y');

        if ($isMyloader) {
            $tasks[] = $this->taskExec($myloader)->arg($dumpFile);
        } else {
            $tasks[] = $this->taskImportDatabase($dumpFile);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Export the local snapshot.
     *
     * This command should be only used with the corporate docker image fpfis/httpd-php.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder|int
     *   Collection builder.
     *
     * @command toolkit:create-dump
     *
     * @option dumpfile The dump file name.
     *
     * @aliases tk-cdump
     */
    public function toolkitCreateDump(ConsoleIO $io, array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ])
    {
        $mydumper = $this->getConfig()->get('toolkit.clone.mydumper');
        if (!file_exists($mydumper)) {
            $io->error('The export script was not found, you must run on the corporate docker image.');
            return ResultData::EXITCODE_ERROR;
        }

        if ($ext = pathinfo($options['dumpfile'], PATHINFO_EXTENSION)) {
            if ($ext !== 'tar') {
                $options['dumpfile'] = str_replace('.' . $ext, '', $options['dumpfile']);
            }
        }
        $dumpFile = $this->tmpDirectory() . '/' . $options['dumpfile'];
        $tasks = [];
        if (file_exists($options['dumpfile'])) {
            $tasks[] = $this->taskFilesystemStack()->remove($dumpFile);
        }

        $tasks[] = $this->taskExec($mydumper)->arg($dumpFile);

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Download database snapshot.
     *
     * @codingStandardsIgnoreStart Generic.Commenting.DocComment.TagsNotGrouped
     *
     * Configuration for database snapshot in NEXTCLOUD.
     * - Environment variables: NEXTCLOUD_USER, NEXTCLOUD_PASS (EU Login).
     * - Runner variables:
     *
     * @code
     * toolkit:
     *   clone:
     *     type: 'nextcloud'
     *     nextcloud:
     *       url: 'files.fpfis.tech.ec.europa.eu/remote.php/dav/files'
     *       services:
     *         - mysql
     *         - solr
     *         - virtuoso
     * @endcode
     *
     * Configuration for database snapshot in custom server.
     * - Runner variables:
     *
     * @code
     * toolkit:
     *   clone:
     *     type: 'custom'
     *     dumpfile: dumpfile.sql.gz
     *     custom:
     *       url: example.com/databases
     *       user: username
     *       pass: secret-password
     * @endcode
     *
     * @codingStandardsIgnoreEnd
     *
     * @command toolkit:download-dump
     *
     * @option is-admin For nextcloud admin user.
     * @option yes      Skip the question to download newer dump.
     *
     * @aliases tk-ddump
     */
    public function toolkitDownloadDump(ConsoleIO $io, array $options = [
        'is-admin' => InputOption::VALUE_NONE,
        'yes' => InputOption::VALUE_NONE,
    ])
    {
        $type = $this->getConfig()->get('toolkit.clone.type', 'nextcloud');
        $this->say("Download type is: $type");
        if ($type === 'nextcloud') {
            return $this->nextcloudDownloadDump($io, $options);
        }
        return $this->customDownloadDump($io, $options);
    }

    /**
     * Download the available services from Nextcloud.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function nextcloudDownloadDump(ConsoleIO $io, array $options)
    {
        // Get the username and password, ask if not present.
        if (empty($user = Toolkit::getNextcloudUser())) {
            if (empty($user = $this->ask('Please insert your username:'))) {
                $io->error('The username cannot be empty!');
                return ResultData::EXITCODE_ERROR;
            }
        }
        if (empty($password = Toolkit::getNextcloudPass())) {
            if (empty($password = $this->askHidden('Please insert your password:'))) {
                $io->error('The password cannot be empty!');
                return ResultData::EXITCODE_ERROR;
            }
        }

        $config = $this->getConfig();
        $tmpFolder = $this->tmpDirectory();
        $opts = ToolCommands::parseOptsYml();
        $isMydumper = isset($opts['mydumper']) && $opts['mydumper'];
        $projectId = $config->get('toolkit.project_id');
        $vendor = $config->get('toolkit.clone.nextcloud.vendor');
        $source = $config->get('toolkit.clone.nextcloud.source');
        $url = $config->get('toolkit.clone.nextcloud.url');
        $services = $config->get('toolkit.clone.nextcloud.services', 'mysql');
        Toolkit::ensureArray($services);

        // Keep backwards compatibility.
        $asdaServices = $config->get('toolkit.clone.asda_services');
        if (!empty($asdaServices)) {
            $io->warning('Using the config ${toolkit.clone.asda_services} is deprecated, please update to ${toolkit.clone.nextcloud.services}.');
            $services = $asdaServices;
            Toolkit::ensureArray($services);
        }

        $isAdmin = !($options['is-admin'] === InputOption::VALUE_NONE) || $config->get('toolkit.clone.nextcloud.admin');
        if (Toolkit::isCiCd()) {
            $isAdmin = true;
        }
        if ($isAdmin) {
            $url .= "/$user/forDevelopment/$vendor/$projectId-$source";
        } else {
            $url .= "/$user/$projectId-$source";
        }
        $downloadLink = $this->addAuthToUrl($url, $user, $password);

        $this->say('Download services: ' . implode(', ', $services));
        $tasks = [];
        foreach ($services as $service) {
            $this->say("Checking service '$service'");
            $dump = $tmpFolder . '/' . $service . ($isMydumper && $service === 'mysql' ? '.tar' : '.gz');
            // Check if the dump is already downloaded.
            if (!file_exists($dump)) {
                $this->say('Starting download');
                $tasks = array_merge($tasks, $this->asdaProcessFile("$downloadLink/$service", $service));
                continue;
            }

            $this->say("File found '$dump', checking server for newer dump");
            if (!$this->nextcloudCheckNewerDump($downloadLink, $service)) {
                $this->say('Local dump is up-to-date');
                continue;
            }
            $question = 'A newer dump was found, would you like to download?';
            if (!Toolkit::isCiCd() && $options['yes'] === InputOption::VALUE_NONE) {
                if (!$this->confirm($question)) {
                    $this->say('Skipping download');
                    continue;
                }
            } else {
                $this->say($question . ' (y/n) Y');
            }
            $this->say('Starting download');
            $tasks = array_merge($tasks, $this->asdaProcessFile("$downloadLink/$service", $service));
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Download the dumpfile from the custom server.
     */
    private function customDownloadDump(ConsoleIO $io, array $options)
    {
        $config = $this->getConfig();
        if (empty($url = $config->get('toolkit.clone.custom.url'))) {
            $io->error('When using custom dump download, you must provide a valid URL in ${toolkit.clone.custom.url}.');
            return ResultData::EXITCODE_ERROR;
        }
        $dumpfile = $config->get('toolkit.clone.dumpfile');
        $user = $config->get('toolkit.clone.custom.user', '') ?? '';
        $password = $config->get('toolkit.clone.custom.pass', '') ?? '';
        $tmpFolder = $this->tmpDirectory();

        // The destination file.
        $destination = "$tmpFolder/$dumpfile";

        // Prepare the final URL containing the dumpfile, user and pass and save into a temp file.
        $link = $this->addAuthToUrl($url, $user, $password);
        $tmpFile = "$tmpFolder/tmp.txt";
        $this->wgetGenerateInputFile("$link/$dumpfile", $tmpFile, true);

        if (file_exists($destination)) {
            $this->say("File found '$destination', checking server for newer dump");
            if (!$this->customCheckNewerDump($tmpFile, $destination)) {
                $this->say('Local dump is up-to-date');
                // Remove temporary file.
                $this->taskExec('rm')->arg($tmpFile)
                    ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                    ->run();
                return ResultData::EXITCODE_OK;
            }

            $question = 'A newer dump was found, would you like to download?';
            if (!Toolkit::isCiCd() && $options['yes'] === InputOption::VALUE_NONE) {
                if (!$this->confirm($question)) {
                    $this->say('Skipping download');
                    // Remove temporary file.
                    $this->taskExec('rm')->arg($tmpFile)
                        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                        ->run();
                    return ResultData::EXITCODE_OK;
                }
            } else {
                $this->say($question . ' (y/n) Y');
            }
        }
        $this->say('Starting download');

        // Download the file.
        $show = $this->getConfigValue('toolkit.clone.show_progress', false);
        $this->wgetDownloadFile($tmpFile, $destination, '.sql.gz,.tar.gz,.tar', !$show)
            ->run();

        // Remove temporary file.
        $this->taskExec('rm')->arg($tmpFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run();

        if (!file_exists($destination) || filesize($destination) === 0) {
            $io->error("Custom : Could not fetch the file $url/$dumpfile");
            // Make sure the dumpfile is deleted if the download failed.
            $this->taskExec('rm')->arg($destination)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run();
            return ResultData::EXITCODE_ERROR;
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Check if a newer dump exists on the Custom server.
     *
     * @param string $tmpFile
     *   The tmp file containing the url for the remote file.
     * @param string $dumpfile
     *   The local dumpfile.
     *
     * @return bool
     *   Return true if the modified date is different between local and remote dumps,
     *   False is case of error or no local file exists.
     */
    private function customCheckNewerDump(string $tmpFile, string $dumpfile): bool
    {
        if (!file_exists($dumpfile)) {
            return false;
        }
        $remote = $this->wgetGetFileModifiedDate($tmpFile);
        if (empty($remote)) {
            return false;
        }
        $remote = date(DATE_RFC2822, strtotime($remote));
        $local = date(DATE_RFC2822, filemtime($dumpfile));
        return strtotime($remote) !== strtotime($local);
    }

    /**
     * Check if a newer dump exists on the Nextcloud server.
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
    private function nextcloudCheckNewerDump(string $link, string $service): bool
    {
        $tmpFolder = $this->tmpDirectory();
        $opts = ToolCommands::parseOptsYml();
        $ext = isset($opts['mydumper']) && $opts['mydumper'] ? '.tar' : '.gz';
        $dump = "$tmpFolder/$service$ext";
        if (!file_exists($dump)) {
            return false;
        }
        $link .= "/$service";
        // Download the .sha file.
        $this->wgetGenerateInputFile("$link/latest.sh1", "$tmpFolder/$service.txt", true);
        $this->wgetDownloadFile("$tmpFolder/$service.txt", "$tmpFolder/$service-latest.sh1", '.sh1', true)
            ->run();
        if (!file_exists("$tmpFolder/$service-latest.sh1")) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return false;
        }
        $latest = file_get_contents("$tmpFolder/$service-latest.sh1");
        if (empty($latest)) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return false;
        }
        $sha1 = trim(explode('  ', $latest)[0]);

        // Remove temporary files.
        $this->taskExec('rm')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->arg("$tmpFolder/$service-latest.sh1")
            ->arg("$tmpFolder/$service.txt")
            ->run();

        // Compare with the local dump.
        if ($sha1 !== sha1_file($dump)) {
            return true;
        }
        return false;
    }

    /**
     * Helper to download and process a Nextcloud dump file.
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
        $tmpFolder = $this->tmpDirectory();

        // Download the .sha file.
        $this->wgetGenerateInputFile("$link/latest.sh1", "$tmpFolder/$service.txt", true);
        $this->wgetDownloadFile("$tmpFolder/$service.txt", "$tmpFolder/$service-latest.sh1", '.sh1', true)
            ->run();
        if (!file_exists("$tmpFolder/$service-latest.sh1")) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return $tasks;
        }
        $latest = file_get_contents("$tmpFolder/$service-latest.sh1");
        if (empty($latest)) {
            $this->writeln("<error>$service : Could not fetch the file latest.sh1</error>");
            return $tasks;
        }
        $filename = trim(explode('  ', $latest)[1]);

        // Display information about ASDA creation date.
        $output = strtoupper($service) . ' DATE: ' . $this->getAsdaDate($filename);
        $separator = str_repeat('=', strlen($output));
        $this->writeln("\n<info>$output\n$separator</info>\n");

        // Download the file.
        $this->wgetGenerateInputFile("$link/$filename", "$tmpFolder/$service.txt", true);
        $extension = str_ends_with($filename, '.gz') ? 'gz' : 'tar';
        $show = $this->getConfigValue('toolkit.clone.show_progress', false);
        $tasks[] = $this->wgetDownloadFile("$tmpFolder/$service.txt", "$tmpFolder/$service.$extension", '.sql.gz,.tar.gz,.tar', !$show);

        // Remove temporary files.
        $tasks[] = $this->taskExec('rm')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->arg("$tmpFolder/$service-latest.sh1")
            ->arg("$tmpFolder/$service.txt");

        return $tasks;
    }

    /**
     * Create a file containing the url to be used by wget --input-file option.
     *
     * @param string $url
     *   Url to fill in the temp file.
     * @param string $tmp
     *   The temporary filename.
     * @param bool $silent
     *   Whether show or not output from task.
     */
    private function wgetGenerateInputFile(string $url, string $tmp, bool $silent = false)
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
     * @return \Robo\Task\Base\Exec
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
     * Get the modified date from the remote file.
     *
     * @param string $tmp
     *   The temporary filename.
     */
    private function wgetGetFileModifiedDate(string $tmp)
    {
        $response = $this->taskExec('wget')
            ->option('-i', $tmp)
            ->option('--server-response')
            ->option('--spider')
            ->rawArg('2>&1 | grep -i Last-Modified')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        return trim(str_replace('Last-Modified:', '', $response));
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
     * Returns a human-readable date of the ASDA dump.
     *
     * @param string $filename
     *   The dump filename that contains the date.
     *
     * @return string
     *   The formatted date, fallback to filename if no date is found.
     */
    private function getAsdaDate(string $filename): string
    {
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
        return $output;
    }

    /**
     * Import given dump file, gunzip is used if dump ends with .gz.
     *
     * @param string $dump
     *   The path to the dump file.
     *
     * @return \Robo\Task\Base\ExecStack
     */
    private function taskImportDatabase(string $dump)
    {
        $mysql = sprintf(
            'mysql -u%s%s -h%s %s',
            getenv('DRUPAL_DATABASE_USERNAME'),
            getenv('DRUPAL_DATABASE_PASSWORD') ? ' -p' . getenv('DRUPAL_DATABASE_PASSWORD') : '',
            getenv('DRUPAL_DATABASE_HOST'),
            getenv('DRUPAL_DATABASE_NAME'),
        );
        if (str_ends_with($dump, '.gz')) {
            $command = "gunzip < $dump | $mysql";
        } else {
            $command = "$mysql < $dump";
        }

        return $this->taskExecStack()->stopOnFail()->silent(true)
            ->exec($command);
    }

    /**
     * Prepare given URL to include the user and pass/token.
     *
     * @param string $url
     *   The URL to process.
     * @param string $user
     *   The user to be added to the URL.
     * @param string $pass
     *   The password or token to be added to the URL.
     */
    private function addAuthToUrl(string $url, string $user, string $pass): string
    {
        // Just return the URL if no user and password are given.
        if (empty($user) && empty($pass)) {
            return $url;
        }
        $scheme = parse_url($url, PHP_URL_SCHEME) ?? 'https';
        $url = str_replace("$scheme://", '', $url);
        return "$scheme://$user:$pass@$url";
    }

}
