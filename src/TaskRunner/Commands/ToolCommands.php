<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\loadTasks;
use Robo\Contract\VerbosityThresholdInterface;
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

        $drush_bin = $this->getBin('drush');
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
        }
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
        if (empty($basicAuth = getenv('QA_API_BASIC_AUTH'))) {
            $this->io()->error('Missing ENV var QA_API_BASIC_AUTH.');
            return 1;
        }

        $this->componentCheckFailed = false;
        $this->componentCheckMandatoryFailed = false;
        $this->componentCheckRecommendedFailed = false;
        $this->componentCheckInsecureFailed = false;
        $this->componentCheckOutdatedFailed = false;
        $this->componentCheckDevVersionFailed = false;
        $this->componentCheckToolkitRequireDev = false;
        $this->componentCheckDrushRequire = false;

        $this->checkCommitMessage();

        $endpointUrl = "https://webgate.ec.europa.eu/fpfis/qa/api/v1/package-reviews?version=8.x";
        $composerJson = file_exists('composer.json') ? json_decode(file_get_contents('composer.json'), true) : false;
        $composerLock = file_exists('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;

        if (!isset($composerLock['packages'])) {
            $this->io()->error('No packages found in the composer.lock file.');
            return 1;
        }

        $status = 0;
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
            $this->say("Evaluation module check passed.");
        }
        echo PHP_EOL;

        $this->io()->title('Checking dev components.');
        foreach ($composerLock['packages'] as $package) {
            $typeBypass = in_array($package['type'], [
                'drupal-custom-module',
                'drupal-custom-theme',
                'drupal-custom-profile',
            ]);
            if (!$typeBypass && preg_match('[^dev\-|\-dev$]', $package['version'])) {
                $this->componentCheckDevVersionFailed = true;
                $this->say("Package {$package['name']}:{$package['version']} cannot be used in dev version.");
            }
        }
        if (!$this->componentCheckDevVersionFailed) {
            $this->say('Dev components check passed.');
        }
        echo PHP_EOL;

        $this->io()->title('Checking require-dev section for Toolkit.');
        foreach ($composerJson['require'] as $name => $package) {
            if ($name == 'ec-europa/toolkit') {
                $this->componentCheckToolkitRequireDev = true;
                $this->say("Package $name cannot be used in require section. Move it to require-dev.");
            }
        }
        if (!$this->componentCheckToolkitRequireDev) {
            $this->say('Toolkit require-dev section check passed.');
        }

        echo PHP_EOL;

        $this->io()->title('Checking require section for Drush.');
        foreach ($composerJson['require-dev'] as $name => $package) {
            if ($name == 'drush/drush') {
                $this->componentCheckDrushRequire = true;
                $this->say("Package $name cannot be used in require-dev. Move it to require section.");
            }
        }
        if (!$this->componentCheckDrushRequire) {
            $this->say('Drush require section check passed.');
        }

        echo PHP_EOL;

        $this->printComponentResults();

        // If the validation fail, return according to the blocker.
        if (
            $this->componentCheckFailed ||
            $this->componentCheckMandatoryFailed ||
            $this->componentCheckRecommendedFailed ||
            $this->componentCheckDevVersionFailed ||
            $this->componentCheckToolkitRequireDev ||
            $this->componentCheckDrushRequire ||
            (!$this->skipInsecure && $this->componentCheckInsecureFailed)
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
        ]);

        return $status;
    }

    /**
     * Helper function to validate the component.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    protected function printComponentResults()
    {
        $this->io()->title('Results:');

        $skipInsecure = ($this->skipInsecure) ? ' (Skipping)' : '';
        $skipOutdated = ($this->skipOutdated) ? '' : ' (Skipping)';

        $msgs[] = 'Mandatory module check ' . ($this->componentCheckMandatoryFailed ? 'failed.' : 'passed.');
        $msgs[] = 'Recommended module check ' . ($this->componentCheckRecommendedFailed ? 'failed.' : 'passed.') . ' (report only)';
        $msgs[] = 'Insecure module check ' . ($this->componentCheckInsecureFailed ? 'failed.' : 'passed.') . $skipInsecure;
        $msgs[] = 'Outdated module check ' . ($this->componentCheckOutdatedFailed ? 'failed.' : 'passed.') . $skipOutdated;
        $msgs[] = 'Dev module check ' . ($this->componentCheckDevVersionFailed ? 'failed.' : 'passed.');
        $msgs[] = 'Evaluation module check ' . ($this->componentCheckFailed ? 'failed.' : 'passed.');
        $msgs[] = 'Toolkit require-dev section check ' . ($this->componentCheckToolkitRequireDev ? 'failed.' : 'passed.');
        $msgs[] = 'Drush require section check ' . ($this->componentCheckDrushRequire ? 'failed.' : 'passed.');

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
        // Get enabled packages.
        $result = $this->taskExec('drush pm-list --fields=status --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $projPackages = json_decode($result, true);
        $enabledPackages = array_keys(array_filter($projPackages, function ($item) {
            return $item['status'] === 'Enabled';
        }));

        // Get mandatory packages.
        $mandatoryPackages = array_column(array_filter($modules, function ($item) {
            return $item['mandatory'] === '1';
        }), 'machine_name');

        $diffMandatory = array_diff($mandatoryPackages, $enabledPackages);
        if (!empty($diffMandatory)) {
            foreach ($diffMandatory as $notPresent) {
                $this->say("Package $notPresent is mandatory and is not present on the project.");
                $this->componentCheckMandatoryFailed = true;
            }
        }
        if (!$this->componentCheckMandatoryFailed) {
            $this->say('Mandatory components check passed.');
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
        if (!$this->componentCheckRecommendedFailed) {
            $this->say('This step is in reporting mode, skipping.');
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    protected function componentInsecure()
    {
        $result = $this->taskExec('drush pm:security --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();

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
        if ($fullSkip) {
            $this->say('Globally Skipping security check for components.');
            $this->componentCheckInsecureFailed = 0;
        } elseif (!$this->componentCheckInsecureFailed) {
            $this->say('Insecure components check passed.');
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    protected function componentOutdated()
    {
        $result = $this->taskExec('composer outdated --direct --minor-only --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();

        $outdatedPackages = json_decode($result, true);

        if (!empty($outdatedPackages['installed'])) {
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

        $fullSkip = getenv('QA_SKIP_OUTDATED') !== false && getenv('QA_SKIP_OUTDATED');
        if ($fullSkip) {
            $this->say('Globally skipping outdated check for components.');
            $this->componentCheckOutdatedFailed = 0;
        } elseif (!$this->componentCheckOutdatedFailed) {
            $this->say('Outdated components check passed.');
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
            return '';
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
     * @return string
     *   The token or false if the request failed.
     */
    public static function getQaSessionToken(): string
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
     * @return string
     *   Empty if could not create session, http code if ok.
     *
     * @throws \Exception
     */
    public static function postQaContent($fields): string
    {
        if (empty($url = getenv('QA_WEBSITE_URL'))) {
            $url = 'https://webgate.ec.europa.eu/fpfis/qa';
        }
        if (!($token = self::getQaSessionToken())) {
            return '';
        }
        $ch = curl_init($url . '/node?_format=hal_json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/hal+json',
            "X-CSRF-Token: $token",
            'Authorization: Basic ' . getenv('QA_API_BASIC_AUTH'),
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return (string) $code;
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
    public function d9Compatibility(): int
    {
        $this->checkCommitMessage();

        if (!$this->skipd9c) {
            $this->say('Developer is skipping Drupal 9 compatibility analysis.');
            return 0;
        }

        if ($drupal_version = $this->getPackagePropertyFromComposer('drupal/core')) {
            if (Semver::satisfies($drupal_version, '^9')) {
                $this->say('Project already running on Drupal 9, skipping Drupal 9 compatibility analysis.');
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
    public function composerLockCheck(): int
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
                'sql:conf', 'sql-conf',
                'sql:connect', 'sql-connect',
                'sql:create', 'sql-create',
                'sql:drop', 'sql-drop',
                'sql:cli', 'sql-cli', 'sqlc',
                'sql:query', 'sql-query', 'sqlq',
                'sql:dump', 'sql-dump',
                'sql:sanitize', 'sql-sanitize', 'sqlsan',
                'sql:sync', 'sql-sync',
                'pm:enable', 'pm-enable', 'en',
                'pm:disable', 'pm-disable', 'dis',
                'user:login', 'user-login', 'uli',
                'user:information', 'user-information', 'uinf',
                'user:block', 'user-block', 'ublk',
                'user:unblock', 'user-unblock', 'uublk',
                'user:role:add', 'user-add-role', 'urol',
                'user:role:remove', 'user-remove-role', 'urrol',
                'user:create', 'user-create', 'ucrt',
                'user:cancel', 'user-cancel', 'ucan',
                'user:password', 'user-password', 'upwd',
                'php:eval', 'php-eval', 'eval', 'ev',
                'composer',
                'git',
                'wget',
                'curl',
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
        $this->skipInsecure = false;
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
                    $this->skipInsecure = true;
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
                    $versionTmp = str_replace($core . "-", "", (string) $releaseItem->version);

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        $php_check = $toolkit_check = $drupal_check = $endpoint_check = $nextcloud_check = $asda_check = 'FAIL';
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
            $isValid = version_compare($php_version, $data['php_version']);
            $php_check = ($isValid >= 0) ? 'OK' : 'FAIL';

            // Handle Toolkit version.
            if (!($toolkit_version = $this->getPackagePropertyFromComposer('ec-europa/toolkit'))) {
                $toolkit_check = 'FAIL (not found)';
            } else {
                $toolkit_check = Semver::satisfies($toolkit_version, $data['toolkit']) ? 'OK' : 'FAIL';
            }
            // Handle Drupal version.
            if (!($drupal_version = $this->getPackagePropertyFromComposer('drupal/core'))) {
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
            curl_setopt($curl, CURLOPT_URL, 'https://git.fpfis.eu/api/v4/users?username=qa-dashboard-api');
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
        if (!empty(getenv('ASDA_USER')) && !empty(getenv('ASDA_PASSWORD'))) {
            $asda_check = 'OK';
        } else {
            $asda_check .= ' (Missing environment variable(s):';
            $asda_check .= empty(getenv('ASDA_USER')) ? ' ASDA_USER' : '';
            $asda_check .= empty(getenv('ASDA_PASSWORD')) ? ' ASDA_PASSWORD' : '';
            $asda_check .= ')';
        }
        // Handle NEXTCLOUD.
        if (!empty(getenv('NEXTCLOUD_USER')) && !empty(getenv('NEXTCLOUD_PASS'))) {
            $nextcloud_check = 'OK';
        } else {
            $nextcloud_check .= ' (Missing environment variable(s):';
            $nextcloud_check .= empty(getenv('NEXTCLOUD_USER')) ? ' NEXTCLOUD_USER' : '';
            $nextcloud_check .= empty(getenv('NEXTCLOUD_PASS')) ? ' NEXTCLOUD_PASS' : '';
            $nextcloud_check .= ')';
        }

        $this->writeln(sprintf(
            "Required checks:
=============================
Checking PHP version: %s (%s)
Checking Toolkit version: %s (%s)
Checking Drupal version: %s (%s)

Optional checks:
=============================
Checking QA Endpoint access: %s
Checking github.com oauth access: %s
Checking git.fpfis.eu oauth access: %s
Checking ASDA configuration: %s
Checking NEXTCLOUD configuration: %s",
            $php_check,
            $php_version,
            $toolkit_check,
            $toolkit_version,
            $drupal_check,
            $drupal_version,
            $endpoint_check,
            $github_check,
            $gitlab_check,
            $asda_check,
            $nextcloud_check
        ));

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

        $settings = $options['drupal_path']  . '/sites/default/settings.php';
        if (file_exists($settings)) {
            $tasks[] = $this->taskExec("chmod 440 $settings");
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Check the Toolkit version.
     *
     * @command toolkit:check-version
     */
    public function toolkitVersion()
    {
        $endpoint = 'https://webgate.ec.europa.eu/fpfis/qa/api/v1/toolkit-requirements';
        $result = self::getQaEndpointContent($endpoint, getenv('QA_API_BASIC_AUTH'));
        $min_version = '';

        if (!($composer_version = $this->getPackagePropertyFromComposer('ec-europa/toolkit'))) {
            $this->writeln('Failed to get Toolkit version from composer.lock.');
        }
        if ($result) {
            $data = json_decode($result, true);
            if (empty($data) || !isset($data['toolkit'])) {
                $this->writeln('Invalid data returned from the endpoint.');
            } else {
                $min_version = $data['toolkit'];
                if ($composer_version) {
                    $major = '' . intval(substr($composer_version, 0, 2));
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

        $version_check = Semver::satisfies($composer_version, $min_version) ? 'OK' : 'FAIL';
        $this->writeln(sprintf(
            "Minimum version: %s\nCurrent version: %s\nVersion check: %s",
            $min_version,
            $composer_version,
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
     * @param $package
     *   The package to search.
     * @param $prop
     *   The property to return. Default to 'version'.
     *
     * @return false|mixed
     *   The property value, false if not found.
     */
    private function getPackagePropertyFromComposer($package, $prop = 'version')
    {
        if (!file_exists('composer.lock')) {
            return false;
        }
        $composer = json_decode(file_get_contents('composer.lock'), true);
        if ($composer) {
            $type = 'packages-dev';
            $index = array_search($package, array_column($composer[$type], 'name'));
            if ($index === false) {
                $type = 'packages';
                $index = array_search($package, array_column($composer[$type], 'name'));
            }
            if ($index !== false && isset($composer[$type][$index][$prop])) {
                return $composer[$type][$index][$prop];
            }
        }
        return false;
    }
}
