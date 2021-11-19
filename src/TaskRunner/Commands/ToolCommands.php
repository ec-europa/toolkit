<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\loadTasks;
use Symfony\Component\Console\Input\InputOption;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Symfony\Component\Yaml\Yaml;

/**
 * Generic tools.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ToolCommands extends AbstractCommands
{
    use loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../../config/commands/tool.yml';
    }

    /**
     * Disable aggregation and clear cache.
     *
     * @command toolkit:disable-drupal-cache
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function disableDrupalCache()
    {
        $tasks = [];

        $drush_bin = $this->getConfig()->get('runner.bin_dir') . '/drush';
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($drush_bin . ' -y config-set system.performance css.preprocess 0')
            ->exec($drush_bin . ' -y config-set system.performance js.preprocess 0')
            ->exec($drush_bin . ' cache:rebuild');

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Display toolkit notifications.
     *
     * @command toolkit:notifications
     *
     * @option endpoint The endpoint for the notifications
     */
    public function displayNotifications(array $options = [
        'endpoint' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $endpointUrl = $options['endpoint'];

        if (isset($endpointUrl)) {
            $result = self::getQaEndpointContent($endpointUrl);
            $data = json_decode($result, true);
            foreach ($data as $notification) {
                $this->io()->warning($notification['title'] . PHP_EOL . $notification['notification']);
            }
        }//end if
    }

    /**
     * Check composer.json for components that are not whitelisted/blacklisted.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @command toolkit:component-check
     *
     * @option endpoint Deprecated
     * @option blocker  Deprecated
     */
    public function componentCheck(array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
        'blocker' => InputOption::VALUE_REQUIRED,
        'test-command' => false,
    ])
    {
        // Currently undocumented in this class. Because I don't know how to
        // provide such a property to one single function other than naming the
        // failed property exactly for this function.
        $this->componentCheckFailed = false;
        $this->componentCheckMandatoryFailed = false;
        $this->componentCheckRecommendedFailed = false;
        $this->componentCheckInsecureFailed = false;
        $this->componentCheckOutdatedFailed = false;

        $this->checkCommitMessage();

        $endpointUrl = "https://webgate.ec.europa.eu/fpfis/qa/api/v1/package-reviews?version=8.x";
        $composerLock = file_exists('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;

        if (empty($basicAuth = getenv('QA_API_BASIC_AUTH'))) {
            $this->io()->warning('Missing ENV var QA_API_BASIC_AUTH.');
            return 1;
        }

        if (isset($endpointUrl) && isset($composerLock['packages'])) {
            $result = self::getQaEndpointContent($endpointUrl, $basicAuth);
            $data = json_decode($result, true);
            $modules = array_filter(array_combine(array_column($data, 'name'), $data));

            // To test this command execute it with the --test-command option:
            // ./vendor/bin/run toolkit:component-check --test-command --endpoint="https://webgate.ec.europa.eu/fpfis/qa/api/v1/package-reviews?version=8.x"
            // Then we provide an array in the packages that fails on each type
            // of validation.
            if ($options['test-command']) {
                $composerLock['packages'] = [
                    // Lines below should trow a warning.
                    ['type' => 'drupal-module', 'version' => '1.0', 'name' => 'drupal/unreviewed'],
                    ['type' => 'drupal-module', 'version' => '1.0', 'name' => 'drupal/devel'],
                    ['type' => 'drupal-module', 'version' => '1.0-alpha1', 'name' => 'drupal/xmlsitemap'],
                    // Allowed for single project jrc-k4p, otherwise trows warning.
                    ['type' => 'drupal-module', 'version' => '1.0', 'name' => 'drupal/active_facet_pills'],
                    // Allowed dev version if the Drupal version meets the
                    // conflict version constraints.
                    [
                        'version' => 'dev-1.x',
                        'type' => 'drupal-module',
                        'name' => 'drupal/views_bulk_operations',
                        'extra' => [
                            'drupal' => [
                                'version' => '8.x-3.4+15-dev',
                            ],
                        ],
                    ],
                ];
            }

            // Execute all checks.
            $checks = [
                'Mandatory',
                'Recommended',
                'Insecure',
                'Outdated',
            ];

            foreach ($checks as $check) {
                $this->io()->title('Checking ' . $check . ' components.');
                $fct = "component" . $check;
                $this->{$fct}($modules, $composerLock['packages']);
                echo PHP_EOL;
            }

            $this->io()->title('Checking evaluation status components.');
            // Proceed with 'blocker' option. Loop over the packages.
            foreach ($composerLock['packages'] as $package) {
                // Check if it's a drupal package.
                // NOTE: Currently only supports drupal packages :(.
                if (substr($package['name'], 0, 7) === 'drupal/') {
                    $this->validateComponent($package, $modules);
                }
            }
            if ($this->componentCheckFailed == false) {
                $this->say("Evaluation module check is OK.");
            }
            echo PHP_EOL;

            $this->printComponentResults();

            $status = 0;
            // If the validation fail, return according to the blocker.
            if (
                $this->componentCheckFailed ||
                $this->componentCheckMandatoryFailed ||
                $this->componentCheckRecommendedFailed ||
                ($this->componentCheckInsecureFailed && $this->skipInsecure) ||
                ($this->componentCheckOutdatedFailed && $this->skipOutdated)
            ) {
                $msg = 'Failed the components check, please verify the report and update the project.';
                $msg .= "\nSee the list of packages at https://webgate.ec.europa.eu/fpfis/qa/package-reviews.";
                $this->io()->error($msg);
                $status = 1;
            }

            // Give feedback if no problems found.
            if (!$status) {
                $this->io()->success('Components checked, nothing to report.');
            }

            $this->io()->text([
                'NOTE: It is possible to bypass the insecure and outdated check by providing a token in the commit message.',
                'The available tokens are:',
                '    - [SKIP-OUTDATED]',
                '    - [SKIP-INSECURE]',
                '    - [SKIP-D9C]',
            ]);

            return $status;
        }//end if
    }

    /**
     * Helper function to validate the component.
     *
     * @param array $package The package to validate.
     * @param array $modules The modules list.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    protected function printComponentResults()
    {
        $this->io()->title('Results:');

        $skipInsegure = ($this->skipInsecure) ? '' : ' (Skipping)';
        $skipOutdated = ($this->skipOutdated) ? '' : ' (Skipping)';

        $msgs[] = ($this->componentCheckMandatoryFailed) ? 'Mandatory module check failed.' : 'Mandatory module check passed.';
        $msgs[] = ($this->componentCheckRecommendedFailed) ? 'Recommended module check failed. (report only)' : 'Recommended module check passed.';
        $msgs[] = ($this->componentCheckInsecureFailed) ? 'Insecure module check failed.' . $skipInsegure : 'Insecure module check passed.' . $skipInsegure;
        $msgs[] = ($this->componentCheckOutdatedFailed) ? 'Outdated module check failed.' . $skipOutdated : 'Outdated module check passed.' . $skipOutdated;
        $msgs[] = ($this->componentCheckFailed) ? 'Evaluation module check failed.' : 'Evaluation module check passed.';

        foreach ($msgs as $msg) {
            $this->say($msg);
        }

        echo PHP_EOL;
    }

    /**
     * Helper function to validate the component.
     *
     * @param array $package The package to validate.
     * @param array $modules The modules list.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    protected function validateComponent($package, $modules)
    {
        $packageName = $package['name'];
        $hasBeenQaEd = isset($modules[$packageName]);
        $wasRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] !== '0';
        $wasNotRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] === '0';
        $packageVersion = isset($package['extra']['drupal']['version']) ? explode('+', str_replace('8.x-', '', $package['extra']['drupal']['version']))[0] : $package['version'];

        // Exclude invalid.
        $packageVersion = in_array($packageVersion, $this->getConfig()->get("toolkit.invalid-versions")) ?  $package['version'] : $packageVersion;

        // Only validate module components for this time.
        if (isset($package['type']) && $package['type'] === 'drupal-module') {
            // If module was not reviewed yet.
            if (!$hasBeenQaEd) {
                $this->say("Package $packageName:$packageVersion has not been reviewed by QA.");
                $this->componentCheckFailed = true;
            }

            // If module was rejected.
            if ($hasBeenQaEd && $wasRejected) {
                $projectId = $this->getConfig()->get("toolkit.project_id");
                $allowedInProject = in_array($projectId, array_map('trim', explode(',', $modules[$packageName]['restricted_use'])));
                // If module was not allowed in project.
                if (!$allowedInProject) {
                    $this->say("Package $packageName:$packageVersion has been rejected by QA.");
                    $this->componentCheckFailed = true;
                }
            }

            if ($wasNotRejected) {
                # Once all projects are using Toolkit >=4.1.0, the 'version' key
                # may be removed from the endpoint: /api/v1/package-reviews.
                $constraints = [ 'whitelist' => false, 'blacklist' => true ];
                foreach ($constraints as $constraint => $result) {
                    $constraintValue = !empty($modules[$packageName][$constraint]) ? $modules[$packageName][$constraint] : null;

                    if (!is_null($constraintValue) && Semver::satisfies($packageVersion, $constraintValue) === $result) {
                        $this->say("Package $packageName:$packageVersion does not meet the $constraint version constraint: $constraintValue.");
                        $this->componentCheckFailed = true;
                    }
                }
            }
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param array $packages The packages to validate.
     * @param array $modules The modules list.
     *
     * @return void
     */
    protected function componentMandatory($modules, $packages)
    {
        foreach ($packages as $package) {
            $projectPackages[] = $package['name'];
        }
        // Option 'mandatory'.

        // Build task collection.
        // $collection = $this->collectionBuilder();
        // $collection->taskExecStack()
        //     ->exec('vendor/bin/drush pm-list --fields=status --format=json')
        //     ->printOutput(false)
        //     ->silent(true)
        //     ->storeState('insecure');
        // $result = $collection->run();
        // $projPackages = (json_decode($result['insecure'], true));
        // foreach ($projPackages as $projPackage => $status) {
        //     if ($status['status'] == 'enabled') {
        //         $projectPackages[] = $projPackage;
        //     }
        // }
        foreach ($modules as $module) {
            if ($module['mandatory'] === '1') {
                $mandatoryPackages[] = $module['name'];
            }
        }
        $diffMandatory = array_diff($mandatoryPackages, $projectPackages);
        if (!empty($diffMandatory)) {
            foreach ($diffMandatory as $notPresent) {
                $this->say("Package $notPresent is mandatory and is not present on the project.");
                $this->componentCheckMandatoryFailed = true;
            }
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param array $packages The packages to validate.
     * @param array $modules The modules list.
     *
     * @return void
     */
    protected function componentRecommended($modules, $packages)
    {
        foreach ($packages as $package) {
            $projectPackages[] = $package['name'];
        }
        foreach ($modules as $module) {
            if ($module['usage'] === 'recommended') {
                $recommendedPackages[] = $module['name'];
            }
        }

        $diffRecommended = array_diff($recommendedPackages, $projectPackages);
        if (!empty($diffRecommended)) {
            foreach ($diffRecommended as $notPresent) {
                $this->say("Package $notPresent is recommended but is not present on the project.");
                $this->componentCheckRecommendedFailed = false;
            }
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param array $packages The packages to validate.
     * @param array $modules The modules list.
     *
     * @return void
     */
    protected function componentInsecure($modules, $packages)
    {
        // Build task collection.
        $collection = $this->collectionBuilder();
        $result = $collection->taskExecStack()
            ->exec('drush pm:security --format=json')
            ->silent(true)
            ->printOutput(false)
            ->storeState('insecure')
            ->run()
            ->getMessage();

        if (strpos(trim((string) $result), 'There are no outstanding security') !== false) {
            $this->say("There are no outstanding security updates.");
        } else {
            $insecurePackages = json_decode($result, true);
            if (is_array($insecurePackages)) {
                foreach ($insecurePackages as $insecurePackage) {
                    $historyTerms = $this->getPackageDetails($insecurePackage['name'], $insecurePackage['version'], '8.x');
                    $packageInsecureConfirmation = true;
                    $msg = "Package {$insecurePackage['name']} have a security update, please update to a safe version.";

                    if (empty($historyTerms['terms']) || !in_array("insecure", $historyTerms['terms'])) {
                        $packageInsecureConfirmation = false;
                        $msg = $msg . " (Confirmation failed, ignored)";
                    }
                    $this->say($msg);
                    $this->componentCheckInsecureFailed = $packageInsecureConfirmation;
                }
            }
        }

        $fullSkip = getenv('QA_SKIP_INSECURE') !== false ? getenv('QA_SKIP_INSECURE') : false;
        // Forcing skip due to issues with the security advisor date detection.
        $fullSkip = true;
        if ($fullSkip) {
            $this->say('Globally Skipping security check for components.');
            $this->componentCheckInsecureFailed = 0;
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param array $packages The packages to validate.
     * @param array $modules The modules list.
     *
     * @return void
     */
    protected function componentOutdated($modules, $packages)
    {
        foreach ($packages as $package) {
            $projectPackages[] = $package['name'];
        }

        $collection = $this->collectionBuilder();
        $result = $collection->taskExecStack()
            ->exec('composer outdated --direct --minor-only --format=json')
            ->printOutput(false)
            ->storeState('outdated')
            ->silent(true)
            ->run()
            ->getMessage();

        $outdatedPackages = json_decode($result, true);

        if (empty($outdatedPackages['installed'])) {
            $this->say("No outdated packages detected.");
        } else {
            if (is_array($outdatedPackages)) {
                foreach ($outdatedPackages['installed'] as $outdatedPackage) {
                    if (!array_key_exists('latest', $outdatedPackage)) {
                        $this->say("Package " . $outdatedPackage['name'] . " does not provide information about last version.");
                    } else {
                        $this->say("Package " . $outdatedPackage['name'] . " with version installed " . $outdatedPackage["version"] . " is outdated, please update to last version - " . $outdatedPackage["latest"]);
                        $this->componentCheckOutdatedFailed = true;
                    }
                }
            }
        }

        $fullSkip = getenv('QA_SKIP_OUTDATED') !== false ? getenv('QA_SKIP_OUTDATED') : false;
        if ($fullSkip) {
            $this->say('Globally skipping outdated check for components.');
            $this->componentCheckOutdatedFailed = 0;
        }
    }

    /**
     * Curl function to access endpoint with or without authentication.
     *
     * This function is made publicly available as a static function for other
     * projects to call. Then we have to maintain less code.
     *
     * @SuppressWarnings(PHPMD.MissingImport)
     *
     * @param string $url The QA endpoint url.
     * @param string $basicAuth The basic auth.
     *
     * @return string
     */
    public static function getQaEndpointContent(string $url, string $basicAuth = ''): string
    {
        if (!($token = self::getQaSessionToken())) {
            return false;
        }

        $content = '';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($basicAuth !== '') {
            $header = [
                'Authorization: Basic ' . $basicAuth,
                "X-CSRF-Token: $token",
            ];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        $result = curl_exec($curl);

        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            switch ($statusCode) {
                // Upon success set the content to be returned.
                case 200:
                    $content = $result;
                    break;
                // Upon other status codes.
                default:
                    if ($basicAuth === '') {
                        throw new \Exception(sprintf('Curl request to endpoint "%s" returned a %u.', $url, $statusCode));
                    }
                    // If we tried with authentication, retry without.
                    $content = self::getQaEndpointContent($url);
            }
        }
        if ($result === false) {
            throw new \Exception(sprintf('Curl request to endpoint "%s" failed.', $url));
        }
        curl_close($curl);

        return $content;
    }

    /**
     * Helper to return the session token.
     *
     * @return bool|string
     *   The token or false if the request failed.
     */
    public static function getQaSessionToken()
    {
        if (empty($url = getenv('QA_WEBSITE_URL'))) {
            $url = 'https://webgate.ec.europa.eu/fpfis/qa';
        }
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_ENCODING       => '',     // handle compressed
            CURLOPT_USERAGENT      => 'Quality Assurance pipeline', // name of client
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT        => 120,    // time-out on response
        );
        $ch = curl_init("$url/session/token");
        curl_setopt_array($ch, $options);
        $token = curl_exec($ch);
        curl_close($ch);
        return $token;
    }

    /**
     * Helper to send a payload to the QA Website.
     *
     * @param array $fields
     *   Data to send.
     *
     * @return bool
     *   True if data was sent properly, false otherwise.
     *
     * @throws \Exception
     */
    public static function postQaContent($fields)
    {
        if (empty($url = getenv('QA_WEBSITE_URL'))) {
            $url = 'https://webgate.ec.europa.eu/fpfis/qa';
        }
        if (!($token = self::getQaSessionToken())) {
            return false;
        }
        $ch = curl_init($url . '/node?_format=hal_json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/hal+json',
            "X-CSRF-Token: $token",
            'Authorization: Basic ' . getenv('QA_WEBSITE_TOKEN'),
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }

    /**
     * Check project compatibility for Drupal 9 upgrade.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * Note: The project configuration should be updated.
     *
     * @command toolkit:d9-compatibility
     *
     */
    public function d9Compatibility()
    {
        $this->checkCommitMessage();

        if (!$this->skipd9c) {
            $this->say("Developer is skipping Drupal 9 compatibility analysis.");
            return 0;
        }

        $lockFile = getcwd() . "/composer.lock";
        if (file_exists($lockFile)) {
            $composerLock = json_decode(file_get_contents($lockFile), true);
            foreach ($composerLock['packages'] as $pkg) {
                if ($pkg['name'] == 'drupal/core') {
                    $DrupalCore = $pkg;
                    break;
                }
            }

            if (Semver::satisfies($DrupalCore['version'], '^9')) {
                $this->say("Project already running on Drupal 9, skipping Drupal 9 compatibility analysis.");
                return 0;
            }
        }

        // Prepare project
        $this->say("Preparing project to run upgrade_status:analyze command.");
        $collection = $this->collectionBuilder();
        $collection->taskComposerRequire()
            ->dependency('phpspec/prophecy-phpunit', '^2')
            ->dependency('drupal/upgrade_status', '^3')
            ->dev()
            ->run();

        $collection = $this->collectionBuilder();
        $collection->taskExecStack()
            ->exec('drush en upgrade_status -y')
            ->run();

        // Collect result details.
        $result = $collection->taskExecStack()
            ->exec('drush upgrade_status:analyze --all')
            ->printOutput(false)
            ->storeState('insecure')
            ->silent(true)
            ->run()
            ->getMessage();

        // Check for results.
        $qaCompatibiltyresult = 0;
        if (is_string($result)) {
            $flags = [
                'Check manually',
                'Fix now',
            ];

            foreach ($flags as $flag) {
                if (strpos($flag, $result) !== false) {
                    $qaCompatibiltyresult = 1;
                }
            }
        }

        if ($qaCompatibiltyresult) {
            $this->say('Looks the project need some attention, please check the report.');
        } else {
            $this->say('Congrats, looks like your project is Drupal 9 compatible. In any case you can check the report below.');
        }

        echo $result . PHP_EOL;
        return $qaCompatibiltyresult;
    }

    /**
     * Check if composer.lock exists on the project root folder.
     *
     * @command toolkit:complock-check
     *
     */
    public function composerLockCheck()
    {
        if (!file_exists('composer.lock')) {
            $this->io()->error("Failed to detect a 'composer.lock' file on root folder.");
            return 1;
        } else {
            $this->say("Detected 'composer.lock' file - Ok.");
            // If the check is ok return '0'.
            return 0;
        }
    }

    /**
     * Check project's .opts.yml file for forbidden commands.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @command toolkit:opts-review
     *
     */
    public function optsReview()
    {
        if (file_exists('.opts.yml')) {
            $parseOptsFile = Yaml::parseFile('.opts.yml');
            // List of commands to prevent the use.
            $forbiddenCommands = [
                'sql:conf',
                'sql-conf',
                'sql:connect',
                'sql-connect',
                'sql:create',
                'sql-create',
                'sql:drop',
                'sql-drop',
                'sql:cli',
                'sql-cli',
                'sqlc',
                'sql:query',
                'sql-query',
                'sqlq',
                'sql:dump',
                'sql-dump',
                'sql:sanitize',
                'sql-sanitize',
                'sqlsan',
                'sql:sync',
                'sql-sync',
                'en',
                'pm-enable',
                'pm:disable',
                'dis',
                'pm-disable',
                'user:login',
                'uli',
                'user-login',
                'user:information',
                'uinf',
                'user-information',
                'user:block',
                'ublk',
                'user-block',
                'user:unblock',
                'uublk',
                'user-unblock',
                'user:role:add',
                'urol',
                'user-add-role',
                'user:role:remove',
                'urrol',
                'user-remove-role',
                'user:create',
                'ucrt',
                'user-create',
                'user:cancel',
                'ucan',
                'user-cancel',
                'user:password',
                'upwd',
                'user-password',
            ];
            $reviewOk = true;

            if (empty($parseOptsFile['upgrade_commands'])) {
                $this->say("The project is using default deploy instructions.");
                return 0;
            }
            if (empty($parseOptsFile['upgrade_commands']['default']) && empty($parseOptsFile['upgrade_commands']['append'])) {
                $this->say("Your structure for the 'upgrade_commands' is invalid.\nSee the documentation at https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/Pipeline+configuration+and+override");
                return 1;
            }

            foreach ($parseOptsFile['upgrade_commands'] as $key => $commands) {
                foreach ($commands as $command) {
                    foreach ($forbiddenCommands as $forbiddenCommand) {
                        if ($key == 'default') {
                            $parsedCommand = explode(" ", $command);
                            if (in_array($forbiddenCommand, $parsedCommand)) {
                                $this->say("The command '$command' is not allowed. Please remove it from 'upgrade_commands' section.");
                                $reviewOk = false;
                            }
                        } else {
                            foreach ($command as $env => $subCommand) {
                                $parsedCommand = explode(" ", $subCommand);
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
            } else {
                $this->say("Review 'opts.yml' file - Ok.");
                // If the review is ok return '0'.
                return 0;
            }
        }
    }

    protected function checkCommitMessage()
    {
        $this->skipOutdated = false;
        $this->skipInsecure = true;
        $this->skipd9c = true;

        $commitMsg = getenv('DRONE_COMMIT_MESSAGE') !== false ? getenv('DRONE_COMMIT_MESSAGE') : '';
        $commitMsg = getenv('CI_COMMIT_MESSAGE') !== false ? getenv('CI_COMMIT_MESSAGE') : $commitMsg;

        preg_match_all('/\[([^\]]*)\]/', $commitMsg, $findTokens);

        if (isset($findTokens[1])) {
            // Transform the message to a single token, last one will win.
            foreach ($findTokens[1] as $token) {
                $transformedToken = strtolower(str_replace('-', '_', $token));

                if ($transformedToken == 'skip_outdated') {
                    $this->skipOutdated = false;
                }
                if ($transformedToken == 'skip_insecure') {
                    $this->skipInsecure = false;
                }
                if ($transformedToken == 'skip_d9c') {
                    $this->skipd9c = false;
                }
            }
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
     * Call release history of d.org to confirm security alert.
     */
    public function getPackageDetails($package, $version, $core)
    {
        $name = explode("/", $package)[1];
        // Drupal core is an exception, we should use '/drupal/current'.
        if ($package === 'drupal/core') {
            $url = 'https://updates.drupal.org/release-history/drupal/current';
        } else {
            $url = 'https://updates.drupal.org/release-history/' . $name . '/' . $core;
        }

        $releaseHistory = $fullReleaseHistory = [];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $header = [
            'Content-Type' => 'application/hal+json'
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($curl);

        if ($result !== false) {
            $fullReleaseHistory[$name] = simplexml_load_string($result);
            $terms = [];
            foreach ($fullReleaseHistory[$name]->releases as $release) {
                foreach ($release as $releaseItem) {
                    $versionTmp = (string) str_replace($core . "-", "", $releaseItem->version);

                    if (!is_null($version) && Semver::satisfies($versionTmp, $version)) {
                        foreach ($releaseItem->terms as $term) {
                            foreach ($term as $termItem) {
                                $terms[] = strtolower((string) $termItem->value);
                            }
                        }

                        $releaseHistory = [
                            'name' => $name,
                            'version' => (string) $releaseItem->versions,
                            'terms' => $terms,
                            'date' => (string) $releaseItem->date,
                        ];
                    }
                }
            }
            return $releaseHistory;
        }

        $this->say("No release history found.");
        return 1;
    }

    /**
     * Check the Toolkit Requirements.
     *
     * @command toolkit:requirements
     *
     * @option endpoint The endpoint to get the requirements.
     */
    public function toolkitRequirements(array $options = [
        'endpoint' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $this->say("Checking Toolkit requirements:\n");
        if (empty($options['endpoint'])) {
            $options['endpoint'] = 'https://webgate.ec.europa.eu/fpfis/qa/api/v1/toolkit-requirements';
        }
        $php_check = $toolkit_check = $drupal_check = 'FAIL';
        $endpoint_check = $github_check = $gitlab_check = $asda_check = 'FAIL';
        $php_version = $toolkit_version = $drupal_version = '';

        $result = self::getQaEndpointContent($options['endpoint'], getenv('QA_API_BASIC_AUTH'));
        if ($result) {
            $endpoint_check = 'OK';
            $data = json_decode($result, true);
            if (empty($data) || !isset($data['toolkit'])) {
                $this->writeln('Invalid data.');
                return 1;
            }

            // Handle PHP version.
            $php_version = phpversion();
            $php_check = -1 === version_compare($php_version, $data['php_version']) ? 'FAIL' : 'OK';

            $composerLock = file_exists('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;
            if ($composerLock) {
                // Handle Toolkit version.
                $index = array_search('ec-europa/toolkit', array_column($composerLock['packages-dev'], 'name'));
                if ($index !== false) {
                    $toolkit_version = $composerLock['packages-dev'][$index]['version'];
                    $data['toolkit'] = explode('|', $data['toolkit']);
                    foreach ($data['toolkit'] as $data_value) {
                        if (ltrim($data_value, '~^<>=!')[0] === $toolkit_version[0]) {
                            if (!(-1 === version_compare($toolkit_version, $data_value))) {
                                $toolkit_check = 'OK';
                                break;
                            }
                        }
                    }
                } else {
                    $toolkit_check = 'FAIL (not found)';
                }

                // Handle Drupal version.
                $index = array_search('drupal/core', array_column($composerLock['packages'], 'name'));
                if ($index !== false) {
                    $drupal_version = $composerLock['packages'][$index]['version'];
                    $data['drupal'] = explode('|', $data['drupal']);
                    foreach ($data['drupal'] as $data_value) {
                        if (ltrim($data_value, '~^<>=!')[0] === $drupal_version[0]) {
                            if (!(-1 === version_compare($drupal_version, $data_value))) {
                                $drupal_check = 'OK';
                                break;
                            }
                        }
                    }
                } else {
                    $drupal_check = 'FAIL (not found)';
                }
            } else {
                $drupal_check = 'FAIL (missing composer.lock)';
            }
        }

        // @todo Handle GitHub.
        // @todo Handle GitLab.

        // Handle ASDA.
        if (!empty(getenv('ASDA_USER')) && !empty(getenv('ASDA_PASSWORD'))) {
            $asda_check = 'OK';
        } else {
            $asda_check .= ' (Missing environment variable(s):';
            $asda_check .= empty(getenv('ASDA_USER')) ? ' ASDA_USER' : '';
            $asda_check .= empty(getenv('ASDA_PASSWORD')) ? ' ASDA_PASSWORD' : '';
            $asda_check .= ')';
        }

        $this->writeln(sprintf(
            "Checking PHP version: %s (%s)
Checking Toolkit version: %s (%s)
Checking Drupal version: %s (%s)

Checking QA Endpoint access: %s
Checking github.com oauth access: %s
Checking git.fpfis.eu oauth access: %s
Checking ASDA configuration: %s",
            $php_check,
            $php_version,
            $toolkit_check,
            $toolkit_version,
            $drupal_check,
            $drupal_version,
            $endpoint_check,
            $github_check,
            $gitlab_check,
            $asda_check
        ));

        if ($php_check !== 'OK' || $toolkit_check !== 'OK' || $drupal_check !== 'OK') {
            return 1;
        }
        return 0;
    }
}
