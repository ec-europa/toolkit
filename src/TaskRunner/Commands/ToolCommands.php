<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Generic tools.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ToolCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/tool.yml';
    }

    /**
     * Check the commit message for SKIPPING tokens.
     *
     * @return array<mixed>
     *   An array with tokens present in the commit message.
     */
    public static function getCommitTokens()
    {
        $tokens = [];
        $commitMsg = getenv('DRONE_COMMIT_MESSAGE') !== false ? getenv('DRONE_COMMIT_MESSAGE') : '';
        $commitMsg = getenv('CI_COMMIT_MESSAGE') !== false ? getenv('CI_COMMIT_MESSAGE') : $commitMsg;
        preg_match_all('/\[([^\]]*)\]/', $commitMsg, $findTokens);
        if (!empty($findTokens[1])) {
            foreach ($findTokens[1] as $token) {
                $transformedToken = strtolower(str_replace('-', '_', $token));
                if ($transformedToken == 'skip_outdated') {
                    $tokens['skipOutdated'] = true;
                }
                if ($transformedToken == 'skip_insecure') {
                    $tokens['skipInsecure'] = true;
                }
                if ($transformedToken == 'skip_d9c') {
                    $tokens['skipDus'] = true;
                }
                if ($transformedToken == 'skip_npm_insecure') {
                    $tokens['skipInsecureNpm'] = true;
                }
            }
        }
        return $tokens;
    }

    /**
     * Check if 'composer.lock' exists on the project root folder.
     *
     * @command toolkit:complock-check
     */
    public function composerLockCheck(ConsoleIO $io): int
    {
        if (!file_exists('composer.lock')) {
            $io->error("Failed to detect a 'composer.lock' file on root folder.");
            return 1;
        }
        $this->say("Detected 'composer.lock' file - Ok.");
        // If the check is ok return '0'.
        return 0;
    }

    /**
     * Check project's .opts.yml file for forbidden commands.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:opts-review
     *
     * @option endpoint The endpoint to use to connect to QA Website.
     * @option junit    Whether to export results as junit.
     *
     * @aliases tk-opts-review
     *
     * @return int
     *   The opts-review command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function optsReview(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
    ])
    {
        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        $reviewOk = true;
        $parseOptsFile = self::parseOptsYml();
        if ($this->isJunit()) {
            JunitXmlGenerator::addTestCase('OPTS review', 'PHP version');
            JunitXmlGenerator::addTestCase('OPTS review', 'Sanitise options');
            JunitXmlGenerator::addTestCase('OPTS review', 'Upgrade commands');
            JunitXmlGenerator::addTestCase('OPTS review', 'Project id');
        }

        // Check for invalid php_version value, if given version is 8.0 as float when converted to string will be 8
        // and will cause issues like in docker images.
        if (!empty($parseOptsFile['php_version']) && is_float($parseOptsFile['php_version'])) {
            if ((string) $parseOptsFile['php_version'] === '8') {
                $message = 'The php_version should be wrapped with upper-quotes like "php_version: \'8.0\'".';
                $io->say($message);
                $reviewOk = false;
                if ($this->isJunit()) {
                    JunitXmlGenerator::addResult('OPTS review', 'PHP version', $message);
                }
            }
        }

        // Check for wrong syntax used for SANITIZE_OPTS.
        if (!empty($parseOptsFile['dump_options'])) {
            if (!empty($parseOptsFile['dump_options']['SANITIZE_OPTS'])) {
                if (!DrupalSanitiseCommands::areUserFieldsSanitised()) {
                    $message = 'Detected forbidden usage of --sanitize-email=no and/or --sanitize-password=no';
                    $io->error($message);
                    $reviewOk = false;
                    if ($this->isJunit()) {
                        JunitXmlGenerator::addResult('OPTS review', 'Sanitise options', $message);
                    }
                }
            }
            if (!empty($parseOptsFile['dump_options'][0])) {
                $message = [
                    'Invalid syntax detected in dump_options section for the SANITIZE_OPTS. Use:',
                    'dump_options:',
                    '  SANITIZE_OPTS: "--sanitize-email=dummy@example.com"',
                ];
                $io->error($message);
                $reviewOk = false;
                if ($this->isJunit()) {
                    JunitXmlGenerator::addResult('OPTS review', 'Sanitise options', implode(PHP_EOL, $message));
                }
            }
        }

        if (empty($parseOptsFile['upgrade_commands'])) {
            $io->say('The project is using default deploy instructions.');
            if ($this->isJunit()) {
                JunitXmlGenerator::generate('junit-opts.xml');
            }
            return $reviewOk ? ResultData::EXITCODE_OK : ResultData::EXITCODE_ERROR;
        }
        if (empty($parseOptsFile['upgrade_commands']['default']) && empty($parseOptsFile['upgrade_commands']['append'])) {
            $message = "Your structure for the 'upgrade_commands' is invalid.\nSee the documentation at https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/Pipeline+configuration+and+override";
            $io->say($message);
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('OPTS review', 'Upgrade commands', $message);
                JunitXmlGenerator::generate('junit-opts.xml');
            }
            return ResultData::EXITCODE_ERROR;
        }

        $projectId = $this->getConfig()->get('toolkit.project_id');
        if (empty($projectId)) {
            $message = 'The configuration toolkit.project_id value is not valid.';
            $io->say($message);
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('OPTS review', 'Project id', $message);
                JunitXmlGenerator::generate('junit-opts.xml');
            }
            return ResultData::EXITCODE_ERROR;
        }

        $forbiddenCommands = Website::projectConstraints($projectId);
        if (empty($forbiddenCommands)) {
            $io->error('Failed to get constraints from the endpoint.');
            return ResultData::EXITCODE_ERROR;
        }
        // Gather all the commands, ignore the 'ephemeral' commands.
        $commands = [];
        if (!empty($parseOptsFile['upgrade_commands']['append']['acceptance'])) {
            $commands = array_merge($commands, $parseOptsFile['upgrade_commands']['append']['acceptance']);
            unset($parseOptsFile['upgrade_commands']['append']['acceptance']);
        }
        if (!empty($parseOptsFile['upgrade_commands']['append']['production'])) {
            $commands = array_merge($commands, $parseOptsFile['upgrade_commands']['append']['production']);
            unset($parseOptsFile['upgrade_commands']['append']['production']);
        }
        $commands = array_unique(array_merge($commands, $parseOptsFile['upgrade_commands']['default'] ?? $parseOptsFile['upgrade_commands']));
        foreach ($commands as $command) {
            $cleanCommand = str_replace(['"', "'", '\\'], '', $command);
            $parsedCommand = preg_split('/[\s;&|]/', $cleanCommand, 0, PREG_SPLIT_NO_EMPTY);
            foreach ($forbiddenCommands as $forbiddenCommand) {
                if (in_array($forbiddenCommand, $parsedCommand)) {
                    $message = "The command '$command' is not allowed. Please remove it from 'upgrade_commands' section.";
                    $io->say($message);
                    $reviewOk = false;
                    if ($this->isJunit()) {
                        JunitXmlGenerator::addResult('OPTS review', 'Upgrade commands', $message);
                    }
                }
            }
        }

        if ($this->isJunit()) {
            JunitXmlGenerator::generate('junit-opts.xml');
        }

        if (!$reviewOk) {
            $io->error("Failed the '.opts.yml' file review. Please contact the QA team.");
            return ResultData::EXITCODE_ERROR;
        }

        $io->say("Review '.opts.yml' file - Ok.");
        return ResultData::EXITCODE_OK;
    }

    /**
     * Check the Toolkit Requirements.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:requirements
     *
     * @option endpoint The endpoint to use to connect to QA Website.
     * @option junit    Whether to export results as junit.
     *
     * @aliases tk-req
     *
     * @return int
     *   The toolkit requirements command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toolkitRequirements(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
    ])
    {
        $this->say("Checking Toolkit requirements:\n");

        $junitFile = 'junit-requirements.xml';
        if ($this->isJunit()) {
            JunitXmlGenerator::addTestSuite('Requirements');
            JunitXmlGenerator::addTestCase('Requirements', 'Endpoint connection');
            JunitXmlGenerator::addTestCase('Requirements', 'NEXTCLOUD configuration');
            JunitXmlGenerator::addTestCase('Requirements', 'PHP version');
            JunitXmlGenerator::addTestCase('Requirements', 'Toolkit version');
            JunitXmlGenerator::addTestCase('Requirements', 'Drupal version');
        }

        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        $data = Website::requirements();
        if (empty($data)) {
            $message = 'Failed to connect to the endpoint ' . Website::url() . '/api/v1/toolkit-requirements';
            $io->error($message);
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('Requirements', 'Endpoint connection', $message);
                JunitXmlGenerator::generate($junitFile);
            }
            return 1;
        }
        if (!isset($data['toolkit'])) {
            $message = 'Invalid data returned from the endpoint.';
            $this->writeln($message);
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('Requirements', 'Endpoint connection', $message);
                JunitXmlGenerator::generate($junitFile);
            }
            return 1;
        }
        $endpointCheck = 'OK';

        // Handle PHP version.
        $phpVersion = phpversion();
        $isValid = version_compare($phpVersion, $data['php_version']);
        $phpCheck = ($isValid >= 0) ? 'OK' : 'FAIL';
        if ($this->isJunit() && $phpCheck === 'FAIL') {
            JunitXmlGenerator::addResult('Requirements', 'PHP version', $phpCheck);
        }

        // Handle Toolkit version.
        if (!($toolkitVersion = self::getPackagePropertyFromComposer('ec-europa/toolkit', 'version', 'packages-dev'))) {
            $toolkitCheck = 'FAIL (not found)';
        } else {
            $toolkitCheck = Semver::satisfies($toolkitVersion, $data['toolkit']) ? 'OK' : 'FAIL';
        }
        if ($this->isJunit() && str_starts_with($toolkitCheck, 'FAIL')) {
            JunitXmlGenerator::addResult('Requirements', 'Toolkit version', $toolkitCheck);
        }
        // Handle Drupal version.
        if (!($drupalVersion = self::getPackagePropertyFromComposer('drupal/core'))) {
            $drupalCheck = 'FAIL (not found)';
        } else {
            $drupalCheck = Semver::satisfies($drupalVersion, $data['drupal']) ? 'OK' : 'FAIL';
        }
        if ($this->isJunit() && str_starts_with($drupalCheck, 'FAIL')) {
            JunitXmlGenerator::addResult('Requirements', 'Drupal version', $drupalCheck);
        }
        // Check if node_install enable in the .opts.yml.
        $parseOptsFile = self::parseOptsYml();
        $nodeCheck = 'OK';
        $nodeVersion = '';
        // Check if npm_install property enabled.
        if (!empty($parseOptsFile['npm_install'])) {
            if ($this->isJunit()) {
                JunitXmlGenerator::addTestCase('Requirements', 'Node version');
            }
            // Check node version running.
            $exec = $this->taskExec('node --version')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run();
            $nodeVersion = rtrim($exec->getMessage());
            if (empty($nodeVersion)) {
                $nodeCheck = 'FAIL (not found)';
            } else {
                $nodeVersion = trim($nodeVersion, 'v');
                $nodeCheck = Semver::satisfies($nodeVersion, $data['node']) ? 'OK' : 'FAIL';
            }

            if ($this->isJunit() && str_starts_with($nodeCheck, 'FAIL')) {
                JunitXmlGenerator::addResult('Requirements', 'Node version', $nodeCheck);
            }
        }

        // Handle NEXTCLOUD.
        $ncUser = Toolkit::getNextcloudUser();
        $ncPass = Toolkit::getNextcloudPass();
        if (!empty($ncUser) && !empty($ncPass)) {
            $nextcloudCheck = 'OK';
        } else {
            $nextcloudCheck = 'FAIL (Missing environment variable(s):';
            $nextcloudCheck .= empty($ncUser) ? ' NEXTCLOUD_USER' : '';
            $nextcloudCheck .= empty($ncPass) ? ' NEXTCLOUD_PASS' : '';
            $nextcloudCheck .= ')';
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('Requirements', 'NEXTCLOUD configuration', $nextcloudCheck);
            }
        }

        $io->title('Checking connections:');
        $io->definitionList(
            ['QA Endpoint access' => $endpointCheck],
            ['NEXTCLOUD configuration' => $nextcloudCheck],
        );
        $toolkitExtra = $drupalExtra = '';
        if ($toolkitVersion && $latestToolkit = self::getPackageLatestVersion(Toolkit::REPOSITORY)) {
            if (!Semver::satisfies($toolkitVersion, $latestToolkit)) {
                $toolkitExtra = " <comment>($latestToolkit available)</>";
            }
        }
        if ($drupalVersion && $latestDrupal = self::getPackageLatestVersion('drupal/core')) {
            if (!Semver::satisfies($drupalVersion, $latestDrupal)) {
                $drupalExtra = " <comment>($latestDrupal available)</>";
            }
        }

        $io->title('Required checks:');
        $headers = ['PHP version', 'Toolkit version', 'Drupal version'];
        $rows = [
            "$phpCheck ($phpVersion)",
            "$toolkitCheck" . (!empty($toolkitVersion) ? " ($toolkitVersion)" : '') . (!empty($toolkitExtra) ? $toolkitExtra : ''),
            "$drupalCheck" . (!empty($drupalVersion) ? " ($drupalVersion)" : '') . (!empty($drupalExtra) ? $drupalExtra : ''),
        ];
        if (!empty($parseOptsFile['npm_install'])) {
            $headers[] = 'Node version';
            $rows[] = $nodeCheck . (!empty($nodeVersion) ? " ($nodeVersion)" : '');
        }
        $io->horizontalTable($headers, [$rows]);

        if ($this->isJunit()) {
            JunitXmlGenerator::generate($junitFile);
        }

        if ($phpCheck !== 'OK' || $toolkitCheck !== 'OK' || $drupalCheck !== 'OK' || $nodeCheck !== 'OK') {
            return 1;
        }
        return 0;
    }

    /**
     * Run script to fix permissions (experimental).
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:fix-permissions
     *
     * @return int|\Robo\Collection\CollectionBuilder
     *   The fix permissions command task status.
     */
    public function fixPermissions(array $options = [
        'drupal_path' => InputOption::VALUE_OPTIONAL,
        'drupal_user' => InputOption::VALUE_OPTIONAL,
        'httpd_group' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $script = Toolkit::getToolkitRoot() . '/resources/scripts/fix-permissions.sh';
        if (!file_exists($script)) {
            $this->say("Script was not found at $script, skipping..");
            return 0;
        }
        $tasks = [];
        if (empty($options['drupal_path'])) {
            $root = $this->getConfig()->get('drupal.root');
            $options['drupal_path'] = getenv('DOCUMENT_ROOT') . '/' . $root;
        }
        if (empty($options['drupal_user'])) {
            $options['drupal_user'] = getenv('DAEMON_USER');
        }
        if (empty($options['httpd_group'])) {
            $options['httpd_group'] = getenv('DAEMON_GROUP');
        }

        $execOptions = [
            'drupal_path' => $options['drupal_path'],
            'drupal_user' => $options['drupal_user'],
            'httpd_group' => $options['httpd_group'],
        ];
        $tasks[] = $this->taskExec($script)->options($execOptions, '=');

        $settings = $options['drupal_path'] . '/sites/default/settings.php';
        if (file_exists($settings)) {
            $tasks[] = $this->taskExec("chmod 440 $settings");
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Check the Toolkit version.
     *
     * @command toolkit:check-version
     *
     * @return int
     *   The toolkit version command status.
     */
    public function toolkitVersion(ConsoleIO $io)
    {
        $io->say("Checking Toolkit version:\n");

        $toolkitVersion = Toolkit::VERSION;
        $data = Website::requirements();
        $minVersion = '';

        if (!(self::getPackagePropertyFromComposer('ec-europa/toolkit'))) {
            $io->warning('Failed to get Toolkit version from composer.lock.');
        }
        if (!empty($data)) {
            if (!isset($data['toolkit'])) {
                $io->writeln('Invalid data returned from the endpoint.');
            } else {
                $minVersion = $data['toolkit'];
                $major = substr($toolkitVersion, 0, strpos($toolkitVersion, '.'));
                $minVersions = array_filter(explode('|', $minVersion), function ($v) use ($major) {
                    return str_contains(substr($v, 0, strpos($v, '.') ?: null), $major);
                });
                if (count($minVersions) === 1) {
                    $minVersion = end($minVersions);
                }
            }
        } else {
            $io->writeln('Failed to connect to the endpoint. Required env var QA_API_AUTH_TOKEN.');
        }

        $versionCheck = Semver::satisfies($toolkitVersion, $minVersion) ? 'OK' : 'FAIL';
        $io->writeln(sprintf(
            "Minimum version: %s\nCurrent version: %s\nLatest version: %s\nVersion check: %s",
            $minVersion,
            $toolkitVersion,
            self::getPackageLatestVersion(Toolkit::REPOSITORY) ?? '<null>',
            $versionCheck
        ));
        if ($versionCheck === 'FAIL') {
            return ResultData::EXITCODE_ERROR;
        }
        return ResultData::EXITCODE_OK;
    }

    /**
     * Helper to return a property from a package in the composer.lock file.
     *
     * @param string $package
     *   The package name to search.
     * @param string $prop
     *   The property to return, default to 'version'.
     * @param string|null $section
     *   Set to 'packages' or 'packages-dev' to filter by section.
     *
     * @return false|mixed
     *   The property value, false if not found.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function getPackagePropertyFromComposer(string $package, string $prop = 'version', ?string $section = null)
    {
        if (!file_exists('composer.lock')) {
            return false;
        }
        if (!empty($GLOBALS['composer.lock'])) {
            $composer = $GLOBALS['composer.lock'];
        } else {
            $composer = json_decode(file_get_contents('composer.lock'), true);
            $GLOBALS['composer.lock'] = $composer;
        }
        if ($composer) {
            if (is_null($section)) {
                $index = false;
                $type = 'packages-dev';
                if (!empty($composer[$type])) {
                    $index = array_search($package, array_column($composer[$type], 'name'));
                }
                if ($index === false) {
                    $type = 'packages';
                    if (!empty($composer[$type])) {
                        $index = array_search($package, array_column($composer[$type], 'name'));
                    }
                }
                if ($index !== false && isset($composer[$type][$index][$prop])) {
                    return $composer[$type][$index][$prop];
                }
            } elseif (isset($composer[$section])) {
                $index = array_search($package, array_column($composer[$section], 'name'));
                if ($index !== false && isset($composer[$section][$index][$prop])) {
                    return $composer[$section][$index][$prop];
                }
            }
        }
        return false;
    }

    /**
     * Returns the latest version of given package.
     *
     * @param string $package
     *   The package to get the latest version, i.e: ec-europa/toolkit.
     *
     * @return null|string
     *   Returns the package version, or null if the package is not found.
     */
    public static function getPackageLatestVersion(string $package)
    {
        $process = Process::fromShellCommandline("composer outdated $package --format=json");
        $process->setTimeout(300);
        $process->run();
        if ($process->getExitCode()) {
            return null;
        }
        $result = trim($process->getOutput());
        if (!empty($result) && $result !== '[]') {
            $data = json_decode($result, true);
            if (!empty($data['latest'])) {
                return $data['latest'];
            }
        }
        return null;
    }

    /**
     * Helper to tell if package is installed.
     *
     * @param string $package
     *   The package name to search.
     *
     * @return bool
     *   True or false if not found.
     */
    public static function isPackageInstalled(string $package): bool
    {
        return !empty(ToolCommands::getPackagePropertyFromComposer($package));
    }

    /**
     * Check 'Vendor' packages being monitored.
     *
     * @command toolkit:vendor-list
     *
     * @return int
     *   The toolkit vendor list command status.
     */
    public function toolkitVendorList(ConsoleIO $io)
    {
        if (empty($data = Website::requirements())) {
            $io->writeln('Failed to connect to the endpoint. Required env var QA_API_AUTH_TOKEN.');
            return ResultData::EXITCODE_ERROR;
        }
        if (!isset($data['vendor_list'])) {
            $io->writeln('Invalid data returned from the endpoint.');
            return ResultData::EXITCODE_ERROR;
        }
        $vendorList = $data['vendor_list'];
        $io->title('Vendors being monitored:');
        $io->writeln($vendorList);
        return ResultData::EXITCODE_OK;
    }

    /**
     * Returns the current environment based on env vars.
     *
     * This command is called during build-dist, the build-dist is called in
     * the create-distribution step during deployments.
     * If CI env var is defined and TAG is available then the environment is
     * 'prod' otherwise is 'acc'. If no CI env var is defined assume 'dev'
     * environment.
     *
     * @return string
     *   The current environment, one of: 'dev', 'acc', 'prod'.
     */
    public static function getDeploymentEnvironment(): string
    {
        if (!getenv('CI')) {
            return 'dev';
        }
        if (getenv('CI_COMMIT_TAG') || getenv('DRONE_TAG')) {
            return 'prod';
        }
        return 'acc';
    }

    /**
     * Helper to convert bytes to human-readable unit.
     *
     * @param int $bytes
     *   The bytes to convert.
     * @param int $precision
     *   The precision for the conversion.
     *
     * @return string
     *   The converted value.
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . $units[$pow];
    }

    /**
     * Install packages present in the .opts.yml file under extra_pkgs section.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:install-dependencies
     *
     * @option packages Specify a list of packages to install instead of read from .opts.yml.
     * @option print    Shows output from apt commands.
     *
     * @return int
     *   The toolkit install-dependencies command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitInstallDependencies(ConsoleIO $io, array $options = [
        'packages' => InputOption::VALUE_REQUIRED,
        'print' => InputOption::VALUE_NONE,
    ])
    {
        $return = 0;
        if (!$this->getConfig()->get('toolkit.install_dependencies')) {
            return $return;
        }

        if (empty($options['packages'])) {
            $opts = self::parseOptsYml();
            $packages = $opts['extra_pkgs'] ?? [];
            if (empty($packages)) {
                return $return;
            }
        } else {
            Toolkit::ensureArray($options['packages']);
            $packages = $options['packages'];
        }

        $io->title('Installing dependencies');
        $print = $options['print'] === true;
        $verbose = $print ? VerbosityThresholdInterface::VERBOSITY_NORMAL : VerbosityThresholdInterface::VERBOSITY_DEBUG;
        $data = $install = [];

        // The command apt list needs the apt update to run.
        $this->taskExec('apt-get update')
            ->setVerbosityThreshold($verbose)->run();

        foreach ($packages as $package) {
            $info = $this->taskExec("apt list $package")
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run()->getMessage();
            // The package is installed if output contains '[installed]'. If
            // the name is not in the output the package was not found.
            if (str_contains($info, '[installed') || str_contains($info, '[upgradable')) {
                $data[$package] = 'already installed';
            } elseif (!str_contains($info, $package)) {
                $data[$package] = 'not found';
                $return = 1;
            } else {
                $install[] = $package;
            }
            if ($print) {
                $io->writeln(["Running apt list $package", $info]);
            }
        }

        if (!empty($install)) {
            // Install the missing packages.
            foreach ($install as $package) {
                $this->taskExec("apt-get install -y --no-install-recommends $package")
                    ->setVerbosityThreshold($verbose)->run();

                // Check if the package was installed.
                $info = $this->taskExec("apt list $package")
                    ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                    ->run()->getMessage();
                if (str_contains($info, '[installed') || str_contains($info, '[upgradable')) {
                    $data[$package] = 'installed';
                } else {
                    $data[$package] = 'fail';
                    $return = 1;
                }
                if ($print) {
                    $io->writeln(["Running apt list $package", $info]);
                }
            }
        }

        $table = new Table($io);
        $table->setHeaders(['Package', 'Status']);
        foreach ($data as $package => $status) {
            $table->addRow([$package, $status]);
        }
        $table->render();
        return $return;
    }

    /**
     * Parses the .opts.yml and returns its contents merged with defaults.
     *
     * @return array<mixed>
     *   An array with the content, the defaults and values found in .opts.yml.
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *   If the file could not be read or the YAML is not valid.
     */
    public static function parseOptsYml(): array
    {
        $opts = '.opts.yml';
        $optsData = [
            'mydumper' => true,
        ];
        if (file_exists($opts)) {
            $optsData += (array) Yaml::parseFile($opts);
        }

        return $optsData;
    }

    /**
     * Display Toolkit notifications.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:notifications
     *
     * @option endpoint The endpoint to use to connect to QA Website.
     *
     * @aliases tk-notifications
     *
     * @return int
     *   The toolkit notifications command status.
     */
    public function toolkitNotifications(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_OPTIONAL,
    ])
    {
        // This command is called from a composer-plugin, so we provide
        // a way to bypass this execution by config.
        if (empty($this->getConfigValue('toolkit.notifications.show'))) {
            return ResultData::EXITCODE_OK;
        }
        // Ignore this command on CI/CD.
        if (Toolkit::isCiCd()) {
            return ResultData::EXITCODE_OK;
        }
        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        if ($notifications = Website::notifications()) {
            foreach ($notifications as $notification) {
                $io->title($notification['title']);
                $io->writeln($notification['notification']);
                if (!empty($notification['url'])) {
                    $io->writeln('See more at: ' . $notification['url']);
                }
                $io->newLine(2);
            }
        }
        return ResultData::EXITCODE_OK;
    }

}
