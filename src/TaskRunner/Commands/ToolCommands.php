<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
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
     * @return array
     *   An array with tokens present in the commit message.
     */
    public static function getCommitTokens()
    {
        $tokens = [];
        $commitMsg = getenv('DRONE_COMMIT_MESSAGE') !== false ? getenv('DRONE_COMMIT_MESSAGE') : '';
        $commitMsg = getenv('CI_COMMIT_MESSAGE') !== false ? getenv('CI_COMMIT_MESSAGE') : $commitMsg;
        preg_match_all('/\[([^\]]*)\]/', $commitMsg, $findTokens);
        if (isset($findTokens[1])) {
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
     * @command toolkit:opts-review
     *
     * @option endpoint The endpoint to use to connect to QA Website.
     *
     * @aliases tk-opts-review
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        if ($parseOptsFile === false) {
            $io->say("The file '.opts.yml' was not found, skipping.");
            return ResultData::EXITCODE_OK;
        }

        // Check for invalid php_version value, if given version is 8.0 as float when converted to string will be 8
        // and will cause issues like in docker images.
        if (!empty($parseOptsFile['php_version']) && is_float($parseOptsFile['php_version'])) {
            if ((string) $parseOptsFile['php_version'] === '8') {
                $io->say('The php_version should be wrapped with upper-quotes like "php_version: \'8.0\'".');
                $reviewOk = false;
            }
        }

        if (empty($parseOptsFile['upgrade_commands'])) {
            $io->say('The project is using default deploy instructions.');
            return $reviewOk ? ResultData::EXITCODE_OK : ResultData::EXITCODE_ERROR;
        }
        if (empty($parseOptsFile['upgrade_commands']['default']) && empty($parseOptsFile['upgrade_commands']['append'])) {
            $io->say("Your structure for the 'upgrade_commands' is invalid.\nSee the documentation at https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/Pipeline+configuration+and+override");
            return ResultData::EXITCODE_ERROR;
        }

        $project_id = $this->getConfig()->get('toolkit.project_id');
        if (empty($project_id)) {
            $io->say('The configuration toolkit.project_id value is not valid.');
            return ResultData::EXITCODE_ERROR;
        }

        $forbiddenCommands = Website::projectConstraints($project_id);
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
                    $io->say("The command '$command' is not allowed. Please remove it from 'upgrade_commands' section.");
                    $reviewOk = false;
                }
            }
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
     * @command toolkit:requirements
     *
     * @option endpoint The endpoint to use to connect to QA Website.
     *
     * @aliases tk-req
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

        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        $data = Website::requirements();
        if (empty($data)) {
            $io->error('Failed to connect to the endpoint ' . Website::url() . '/api/v1/toolkit-requirements');
            return 1;
        }
        if (!isset($data['toolkit'])) {
            $this->writeln('Invalid data returned from the endpoint.');
            return 1;
        }
        $endpoint_check = 'OK';

        // Handle PHP version.
        $php_version = phpversion();
        $isValid = version_compare($php_version, $data['php_version']);
        $php_check = ($isValid >= 0) ? 'OK' : 'FAIL';

        // Handle Toolkit version.
        if (!($toolkit_version = self::getPackagePropertyFromComposer('ec-europa/toolkit', 'version', 'packages-dev'))) {
            $toolkit_check = 'FAIL (not found)';
        } else {
            $toolkit_check = Semver::satisfies($toolkit_version, $data['toolkit']) ? 'OK' : 'FAIL';
        }
        // Handle Drupal version.
        if (!($drupal_version = self::getPackagePropertyFromComposer('drupal/core'))) {
            $drupal_check = 'FAIL (not found)';
        } else {
            $drupal_check = Semver::satisfies($drupal_version, $data['drupal']) ? 'OK' : 'FAIL';
        }

        // Handle NEXTCLOUD.
        $nc_user = Toolkit::getNExtcloudUser();
        $nc_pass = Toolkit::getNExtcloudPass();
        if (!empty($nc_user) && !empty($nc_pass)) {
            $nextcloud_check = 'OK';
        } else {
            $nextcloud_check = 'FAIL (Missing environment variable(s):';
            $nextcloud_check .= empty($nc_user) ? ' NEXTCLOUD_USER' : '';
            $nextcloud_check .= empty($nc_pass) ? ' NEXTCLOUD_PASS' : '';
            $nextcloud_check .= ')';
        }

        $io->title('Checking connections:');
        $io->definitionList(
            ['QA Endpoint access' => $endpoint_check],
            ['NEXTCLOUD configuration' => $nextcloud_check],
        );

        $io->title('Required checks:');
        $io->definitionList(
            ['PHP version' => "$php_check ($php_version)"],
            ['Toolkit version' => "$toolkit_check ($toolkit_version)"],
            ['Drupal version' => "$drupal_check ($drupal_version)"],
        );

        if ($php_check !== 'OK' || $toolkit_check !== 'OK' || $drupal_check !== 'OK') {
            return 1;
        }
        return 0;
    }

    /**
     * Run script to fix permissions (experimental).
     *
     * @command toolkit:fix-permissions
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toolkitVersion(ConsoleIO $io)
    {
        $io->say("Checking Toolkit version:\n");

        $toolkit_version = Toolkit::VERSION;
        $data = Website::requirements();
        $min_version = '';

        if (!(self::getPackagePropertyFromComposer('ec-europa/toolkit'))) {
            $io->warning('Failed to get Toolkit version from composer.lock.');
        }
        if (!empty($data)) {
            if (!isset($data['toolkit'])) {
                $io->writeln('Invalid data returned from the endpoint.');
            } else {
                $min_version = $data['toolkit'];
                $major = substr($toolkit_version, 0, strpos($toolkit_version, '.'));
                $min_versions = array_filter(explode('|', $min_version), function ($v) use ($major) {
                    return str_contains(substr($v, 0, strpos($v, '.') ?: null), $major);
                });
                if (count($min_versions) === 1) {
                    $min_version = end($min_versions);
                }
            }
        } else {
            $io->writeln('Failed to connect to the endpoint. Required env var QA_API_AUTH_TOKEN.');
        }

        $version_check = Semver::satisfies($toolkit_version, $min_version) ? 'OK' : 'FAIL';
        $io->writeln(sprintf(
            "Minimum version: %s\nCurrent version: %s\nVersion check: %s",
            $min_version,
            $toolkit_version,
            $version_check
        ));
        if ($version_check === 'FAIL') {
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
    public static function getPackagePropertyFromComposer(string $package, string $prop = 'version', string $section = null)
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
     * Execute all or specific tools for static testing.
     *
     * If no option is given, all the tests will be executed.
     *
     * @command toolkit:code-review
     *
     * @option phpcs       Execute the command toolkit:test-phpcs.
     * @option opts-review Execute the command toolkit:opts-review.
     * @option lint-php    Execute the command toolkit:lint-php.
     * @option lint-yaml   Execute the command toolkit:lint-yaml.
     * @option lint-js     Execute the command toolkit:lint-js.
     * @option phpstan     Execute the command toolkit:test-phpstan.
     * @option phpmd       Execute the command toolkit:test-phpmd.
     * @option phpunit     Execute the command toolkit:test-phpunit.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitCodeReview(ConsoleIO $io, array $options = [
        'phpcs' => InputOption::VALUE_NONE,
        'opts-review' => InputOption::VALUE_NONE,
        'lint-php' => InputOption::VALUE_NONE,
        'lint-yaml' => InputOption::VALUE_NONE,
        'lint-js' => InputOption::VALUE_NONE,
        'phpstan' => InputOption::VALUE_NONE,
        'phpmd' => InputOption::VALUE_NONE,
        'phpunit' => InputOption::VALUE_NONE,
    ])
    {
        $tasks = [
            'PHPcs' => ['cmd' => 'tk-phpcs', 'exec' => $options['phpcs'] === true, 'result' => []],
            'Opts review' => ['cmd' => 'tk-opts-review', 'exec' => $options['opts-review'] === true, 'result' => []],
            'Lint PHP' => ['cmd' => 'tk-php', 'exec' => $options['lint-php'] === true, 'result' => []],
            'Lint YAML' => ['cmd' => 'tk-yaml', 'exec' => $options['lint-yaml'] === true, 'result' => []],
            'Lint JS' => ['cmd' => 'tk-js', 'exec' => $options['lint-js'] === true, 'result' => []],
            'PHPStan' => ['cmd' => 'tk-phpstan', 'exec' => $options['phpstan'] === true, 'result' => []],
            'PHPMD' => ['cmd' => 'tk-phpmd', 'exec' => $options['phpmd'] === true, 'result' => []],
            'PHPUnit' => ['cmd' => 'tk-phpunit', 'exec' => $options['phpunit'] === true, 'result' => []],
        ];
        $exit = 0;
        $runAll = false;
        // If no option is given, run all commands.
        if (empty(array_filter(array_column($tasks, 'exec')))) {
            $runAll = true;
        }
        $run = $this->getBin('run');
        foreach ($tasks as $name => &$task) {
            if ($runAll || $task['exec']) {
                $code = $this->taskExec($run)->arg($task['cmd'])->run()->getExitCode();
                $task['result'] = [$name => $code > 0 ? 'failed' : 'passed'];
                $exit += $code;
                $io->newLine(2);
            } else {
                $task['result'] = [$name => 'skip'];
            }
        }
        $io->title('Results:');
        $io->definitionList(
            $tasks['PHPcs']['result'],
            $tasks['Opts review']['result'],
            $tasks['Lint PHP']['result'],
            $tasks['Lint YAML']['result'],
            $tasks['Lint JS']['result'],
            $tasks['PHPStan']['result'],
            $tasks['PHPMD']['result'],
            $tasks['PHPUnit']['result']
        );

        return new ResultData($exit);
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
     * Install packages present in the opts.yml file under extra_pkgs section.
     *
     * @command toolkit:install-dependencies
     *
     * @option print Shows output from apt commands.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitInstallDependencies(ConsoleIO $io, array $options = [
        'print' => InputOption::VALUE_NONE,
    ])
    {
        $return = 0;
        if (!$this->getConfig()->get('toolkit.install_dependencies')) {
            return $return;
        }
        if (!file_exists('.opts.yml')) {
            return $return;
        }
        $opts = Yaml::parseFile('.opts.yml');
        $packages = $opts['extra_pkgs'] ?? [];
        if (empty($packages)) {
            return $return;
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
            if (str_contains($info, '[installed]')) {
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
                if (str_contains($info, '[installed]')) {
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
     * Returns the .opts.yml file content.
     *
     * @return array|false
     *   An array with the content or false if the file do not exist.
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *   If the file could not be read or the YAML is not valid.
     */
    public static function parseOptsYml()
    {
        $opts = '.opts.yml';
        if (!file_exists($opts)) {
            return false;
        }
        return (array) Yaml::parseFile($opts);
    }

}
