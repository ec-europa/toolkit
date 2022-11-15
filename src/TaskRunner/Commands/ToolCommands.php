<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
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
    public function composerLockCheck(): int
    {
        if (!file_exists('composer.lock')) {
            $this->io()->error("Failed to detect a 'composer.lock' file on root folder.");
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function optsReview()
    {
        if (file_exists('.opts.yml')) {
            if (empty($basicAuth = Website::basicAuth())) {
                return 1;
            }
            $project_id = $this->getConfig()->get('toolkit.project_id');
            $url = Website::url();
            $url .= '/api/v1/project/ec-europa/' . $project_id . '-reference/information/constraints';
            $result = Website::get($url, $basicAuth);
            $result = json_decode($result, true);
            if (!isset($result['constraints'])) {
                $this->io()->error('Failed to get constraints from the endpoint.');
                return 1;
            }
            $forbiddenCommands = $result['constraints'];

            $parseOptsFile = Yaml::parseFile('.opts.yml');
            $reviewOk = true;

            if (empty($parseOptsFile['upgrade_commands'])) {
                $this->say('The project is using default deploy instructions.');
                return 0;
            }
            if (empty($parseOptsFile['upgrade_commands']['default']) && empty($parseOptsFile['upgrade_commands']['append'])) {
                $this->say("Your structure for the 'upgrade_commands' is invalid.\nSee the documentation at https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/Pipeline+configuration+and+override");
                return 1;
            }

            foreach ($parseOptsFile['upgrade_commands'] as $key => $commands) {
                foreach ($commands as $command) {
                    $command = str_replace('\\', '', $command);
                    foreach ($forbiddenCommands as $forbiddenCommand) {
                        if ($key == 'default') {
                            $parsedCommand = preg_split("/[\s;&|]/", $command, 0, PREG_SPLIT_NO_EMPTY);
                            if (in_array($forbiddenCommand, $parsedCommand)) {
                                $this->say("The command '$command' is not allowed. Please remove it from 'upgrade_commands' section.");
                                $reviewOk = false;
                            }
                        } else {
                            foreach ($command as $subCommand) {
                                $parsedCommand = preg_split("/[\s;&|]/", $subCommand, 0, PREG_SPLIT_NO_EMPTY);
                                if (in_array($forbiddenCommand, $parsedCommand)) {
                                    $this->say("The command '$subCommand' is not allowed. Please remove it from 'upgrade_commands' section.");
                                    $reviewOk = false;
                                }
                            }
                        }
                    }
                }
            }
            if ($reviewOk == false) {
                $this->io()->error("Failed the '.opts.yml' file review. Please contact the QA team.");
                return 1;
            }
            $this->say("Review 'opts.yml' file - Ok.");
            // If the review is ok return '0'.
            return 0;
        }
    }

    /**
     * Copy the needed resources to run Behat with Blackfire.
     *
     * @command toolkit:setup-blackfire-behat
     */
    public function setupBlackfireBehat()
    {
        // Check requirement if blackfire/php-sdk exist.
        if (!class_exists('Blackfire\Client')) {
            $this->say('Please install blackfire/php-sdk before continue.');
            return 0;
        }

        $from = $this->getConfig()->get('toolkit.test.behat.from');
        $blackfire_dir = __DIR__ . '/../../../resources/Blackfire';
        $parseBehatYml = Yaml::parseFile($from);
        if (isset($parseBehatYml['blackfire'])) {
            $this->say('Blackfire profile was found, skipping.');
        } else {
            // Append the Blackfire profile to the behat.yml file.
            $this->taskWriteToFile($from)->append(true)
                ->line('# Toolkit auto-generated profile for Blackfire.')
                ->text(file_get_contents("$blackfire_dir/blackfire.behat.yml"))
                ->line('# End Toolkit.')
                ->run();
        }

        // Add the test feature to the tests folder.
        if (file_exists('tests/features/blackfire.feature')) {
            $this->say('Blackfire test feature was found, skipping.');
        } else {
            $this->_copy("$blackfire_dir/blackfire.feature", 'tests/features/blackfire.feature');
        }

        // Add the Blackfire Context to the Context folder.
        if (file_exists('tests/Behat/BlackfireMinkContext.php')) {
            $this->say('Blackfire Mink context was found, skipping.');
        } else {
            $this->_copy("$blackfire_dir/BlackfireMinkContext.php", 'tests/Behat/BlackfireMinkContext.php');
        }

        return 0;
    }

    /**
     * Check the Toolkit Requirements.
     *
     * @command toolkit:requirements
     *
     * @option endpoint The endpoint to get the requirements.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toolkitRequirements(array $options = [
        'endpoint' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $this->say("Checking Toolkit requirements:\n");

        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        $php_check = $toolkit_check = $drupal_check = $endpoint_check = $nextcloud_check = $asda_check = 'FAIL';
        $php_version = $toolkit_version = $drupal_version = '';

        if (empty($basicAuth = Website::basicAuth())) {
            return 1;
        }
        $result = Website::get(Website::url() . '/api/v1/toolkit-requirements', $basicAuth);
        if ($result) {
            $endpoint_check = 'OK';
            $data = json_decode($result, true);
            if (empty($data) || !isset($data['toolkit'])) {
                $this->writeln('Invalid data.');
                return 1;
            }

            // Handle PHP version.
            $php_version = phpversion();
            $isValid = version_compare($php_version, $data['php_version']);
            $php_check = ($isValid >= 0) ? 'OK' : 'FAIL';

            // Handle Toolkit version.
            $toolkit_version = Toolkit::VERSION;
            $toolkit_check = Semver::satisfies($toolkit_version, $data['toolkit']) ? 'OK' : 'FAIL';
            // Handle Drupal version.
            if (!($drupal_version = self::getPackagePropertyFromComposer('drupal/core'))) {
                $drupal_check = 'FAIL (not found)';
            } else {
                $drupal_check = Semver::satisfies($drupal_version, $data['drupal']) ? 'OK' : 'FAIL';
            }
        }

        // Handle GitHub.
        if (empty($token = getenv('GITHUB_API_TOKEN'))) {
            $github_check = 'FAIL (Missing environment variable: GITHUB_API_TOKEN)';
        } else {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://api.github.com/user');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Token $token"]);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Quality Assurance');
            $result = curl_exec($curl);
            $result = (array) json_decode($result);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code === 200) {
                if (isset($result['private_gists'])) {
                    $github_check = "OK ($code)";
                } else {
                    $github_check = "OK ($code) - No private data";
                }
            } else {
                $github_check = "FAIL ($code) " . trim($result['message']);
            }
        }

        // Handle GitLab.
        if (empty($token = getenv('GITLAB_API_TOKEN'))) {
            $gitlab_check = 'FAIL (Missing environment variable: GITLAB_API_TOKEN)';
        } else {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://git.fpfis.tech.ec.europa.eu/api/v4/users?username=qa-dashboard-api');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["PRIVATE-TOKEN: $token"]);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Quality Assurance');
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            $result = curl_exec($curl);
            $result = (array) json_decode($result);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code === 200) {
                $gitlab_check = "OK ($code)";
            } else {
                $gitlab_check = "FAIL ($code) " . trim($result['message']);
            }
        }

        // Handle ASDA.
        $asda_user = Toolkit::getAsdaUser();
        $asda_pass = Toolkit::getAsdaPass();
        if (!empty($asda_user) && !empty($asda_pass)) {
            $asda_check = 'OK';
        } else {
            $asda_check .= ' (Missing environment variable(s):';
            $asda_check .= empty($asda_user) ? ' ASDA_USER' : '';
            $asda_check .= empty($asda_pass) ? ' ASDA_PASSWORD' : '';
            $asda_check .= ')';
        }
        // Handle NEXTCLOUD.
        $nc_user = Toolkit::getNExtcloudUser();
        $nc_pass = Toolkit::getNExtcloudPass();
        if (!empty($nc_user) && !empty($nc_pass)) {
            $nextcloud_check = 'OK';
        } else {
            $nextcloud_check .= ' (Missing environment variable(s):';
            $nextcloud_check .= empty($nc_user) ? ' NEXTCLOUD_USER' : '';
            $nextcloud_check .= empty($nc_pass) ? ' NEXTCLOUD_PASS' : '';
            $nextcloud_check .= ')';
        }

        $this->io()->title('Checking connections:');
        $this->io()->definitionList(
            ['QA Endpoint access' => $endpoint_check],
            ['GitHub oauth access' => $github_check],
            ['GitLab oauth access' => $gitlab_check],
            ['ASDA configuration' => $asda_check],
            ['NEXTCLOUD configuration' => $nextcloud_check],
        );

        $this->io()->title('Required checks:');
        $this->io()->definitionList(
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
        $script = __DIR__ . '/../../../resources/scripts/fix-permissions.sh';
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

        $params = [
            '--drupal_path=' . $options['drupal_path'],
            '--drupal_user=' . $options['drupal_user'],
            '--httpd_group=' . $options['httpd_group'],
        ];
        $command = $script . ' ' . implode(' ', $params);
        $tasks[] = $this->taskExec($command);

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
    public function toolkitVersion()
    {
        $this->say("Checking Toolkit version:\n");

        $url = Website::url();
        $endpoint = $url . '/api/v1/toolkit-requirements';
        if (empty($basicAuth = Website::basicAuth())) {
            return 1;
        }
        $toolkit_version = Toolkit::VERSION;

        $result = Website::get($endpoint, $basicAuth);
        $min_version = '';

        if (!(self::getPackagePropertyFromComposer('ec-europa/toolkit'))) {
            $this->writeln('Failed to get Toolkit version from composer.lock.');
        }
        if ($result) {
            $data = json_decode($result, true);
            if (empty($data) || !isset($data['toolkit'])) {
                $this->writeln('Invalid data returned from the endpoint.');
            } else {
                $min_version = $data['toolkit'];
                if ($toolkit_version) {
                    $major = '' . intval(substr($toolkit_version, 0, 2));
                    $min_versions = array_filter(explode('|', $min_version), function ($v) use ($major) {
                        return strpos(substr($v, 0, 2), $major) !== false;
                    });
                    if (count($min_versions) === 1) {
                        $min_version = end($min_versions);
                    }
                }
            }
        } else {
            $this->writeln('Failed to connect to the endpoint. Required env var QA_API_BASIC_AUTH.');
        }

        $version_check = Semver::satisfies($toolkit_version, $min_version) ? 'OK' : 'FAIL';
        $this->writeln(sprintf(
            "Minimum version: %s\nCurrent version: %s\nVersion check: %s",
            $min_version,
            $toolkit_version,
            $version_check
        ));
        if ($version_check === 'FAIL') {
            return 1;
        }
        return 0;
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
                $type = 'packages-dev';
                $index = array_search($package, array_column($composer[$type], 'name'));
                if ($index === false) {
                    $type = 'packages';
                    $index = array_search($package, array_column($composer[$type], 'name'));
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
     * Check 'Vendor' packages being monitorised.
     *
     * @command toolkit:vendor-list
     */
    public function toolkitVendorList()
    {
        if (empty($basicAuth = Website::basicAuth())) {
            return 1;
        }
        $result = Website::get(Website::url() . '/api/v1/toolkit-requirements', $basicAuth);

        if ($result) {
            $data = json_decode($result, true);
            if (empty($data) || !isset($data['vendor_list'])) {
                $this->writeln('Invalid data returned from the endpoint.');
                return 1;
            }
            $vendorList = $data['vendor_list'];
            $this->io()->title('Vendors being monitorised:');
            $this->writeln($vendorList);
            return 0;
        }
        $this->writeln('Failed to connect to the endpoint. Required env var QA_API_BASIC_AUTH.');
        return 1;
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
     * This command will execute all the testing tools.
     *
     * If no option is given, all the tests will be executed.
     *
     * @command toolkit:code-review
     *
     * @option phpcs        Execute the command toolkit:test-phpcs.
     * @option opts-review  Execute the command toolkit:opts-review.
     * @option lint-php     Execute the command toolkit:lint-php.
     * @option lint-yaml    Execute the command toolkit:lint-yaml.
     * @option phpstan      Execute the command toolkit:test-phpstan.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitCodeReview(array $options = [
        'phpcs' => InputOption::VALUE_NONE,
        'opts-review' => InputOption::VALUE_NONE,
        'lint-php' => InputOption::VALUE_NONE,
        'lint-yaml' => InputOption::VALUE_NONE,
        'phpstan' => InputOption::VALUE_NONE,
    ])
    {
        // If at least one option is given, use given options, else use all.
        $phpcsResult = $optsReviewResult = $lintPhpResult = $lintYamlResult = $phpStanResult = [];
        $phpcs = $options['phpcs'] === true;
        $optsReview = $options['opts-review'] === true;
        $lintPhp = $options['lint-php'] === true;
        $lintYaml = $options['lint-yaml'] === true;
        $phpStan = $options['phpstan'] === true;
        $exit = 0;

        if ($phpcs || $optsReview || $lintPhp || $lintYaml || $phpStan) {
            // Run given checks.
            $runPhpcs = $phpcs;
            $runOptsReview = $optsReview;
            $runLintPhp = $lintPhp;
            $runLintYaml = $lintYaml;
            $runPhpStan = $phpStan;
        } else {
            // Run all checks.
            $runPhpcs = $runOptsReview = $runLintPhp = $runLintYaml = $runPhpStan = true;
        }
        $run = $this->getBin('run');
        if ($runPhpcs) {
            $code = $this->taskExec($run)->arg('toolkit:test-phpcs')
                ->run()->getExitCode();
            $phpcsResult = ['PHPcs' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }
        if ($runOptsReview) {
            $code = $this->taskExec($run)->arg('toolkit:opts-review')
                ->run()->getExitCode();
            $optsReviewResult = ['Opts review' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }
        if ($runLintPhp) {
            $code = $this->taskExec($run)->arg('toolkit:lint-php')
                ->run()->getExitCode();
            $lintPhpResult = ['Lint PHP' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }
        if ($runLintYaml) {
            $code = $this->taskExec($run)->arg('toolkit:lint-yaml')
                ->run()->getExitCode();
            $lintYamlResult = ['Lint YAML' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }
        if ($runPhpStan) {
            $code = $this->taskExec($run)->arg('toolkit:test-phpstan')
                ->run()->getExitCode();
            $phpStanResult = ['PHPStan' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }

        $this->io()->title('Results:');
        $this->io()->definitionList($phpcsResult, $optsReviewResult, $lintPhpResult, $lintYamlResult, $phpStanResult);

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
     * @option print  Shows output from apt commands.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitInstallDependencies(array $options = [
        'print' => InputOption::VALUE_NONE,
    ])
    {
        $this->io()->title('Installing dependencies');
        $return = 0;
        if (!file_exists('.opts.yml')) {
            return $return;
        }
        $opts = Yaml::parseFile('.opts.yml');
        $packages = $opts['extra_pkgs'] ?? [];
        if (empty($packages)) {
            $this->output()->writeln('No packages found, skipping.');
            return $return;
        }

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
                $this->output()->writeln(["Running apt list $package", $info]);
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
                    $this->output()->writeln(["Running apt list $package", $info]);
                }
            }
        }

        $table = new Table($this->io());
        $table->setHeaders(['Package', 'Status']);
        foreach ($data as $package => $status) {
            $table->addRow([$package, $status]);
        }
        $table->render();
        return $return;
    }

}
