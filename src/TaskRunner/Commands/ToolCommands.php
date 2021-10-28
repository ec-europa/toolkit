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
     * @option endpoint The endpoint for the components whitelist/blacklist
     * @option blocker  Whether the command should exit with errorstatus
     */
    public function componentCheck(array $options = [
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
        $composerLock = file_get_contents('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;

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
                $this->say('Checking ' . $check . ' components.');
                $fct = "component" . $check;
                $this->{$fct}($modules, $composerLock['packages']);
            }

            // Proceed with 'blocker' option. Loop over the packages.
            foreach ($composerLock['packages'] as $package) {
                // Check if it's a drupal package.
                // NOTE: Currently only supports drupal packages :(.
                if (substr($package['name'], 0, 7) === 'drupal/') {
                    $this->validateComponent($package, $modules);
                }
            }

            // If the validation fail, return according to the blocker.
            if ($this->componentCheckFailed ||
                $this->componentCheckMandatoryFailed ||
                $this->componentCheckRecommendedFailed ||
                ($this->componentCheckInsecureFailed && $this->skipInsecure) ||
                ($this->componentCheckOutdatedFailed && $this->skipOutdated)
            ) {
                $msg = 'Failed the components check, please verify the report and update the project.';
                $msg .= "\nSee the list of packages at https://webgate.ec.europa.eu/fpfis/qa/package-reviews.";
                $this->io()->warning($msg);
                return 1;
            }

            // Give feedback if no problems found.
            $this->io()->success('Components checked, nothing to report.');
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
        $collection = $this->collectionBuilder();
        $collection->taskExecStack()
            ->exec('vendor/bin/drush pm-list --fields=status --format=json')
            ->printOutput(false)
            ->storeState('insecure');
        $result = $collection->run();
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
                $this->io()->warning("Package $notPresent is mandatory and is not present on the project.");
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
        $recommendedPackages[] = $module['name'];
        $diffRecommended = array_diff($recommendedPackages, $projectPackages);
        if (!empty($diffRecommended)) {
            foreach ($diffRecommended as $notPresent) {
                $this->io()->note("Package $notPresent is recommended but is not present on the project.");
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
            ->printOutput(false)
            ->storeState('insecure')
            ->silent(true)
            ->run()
            ->getMessage();

        if (strpos(trim((string) $result), 'There are no outstanding security') !== false) {
            $this->io()->note("There are no outstanding security updates.");
        } else {
            $insecurePackages = json_decode($result, true);
            if (is_array($insecurePackages)) {
                foreach ($insecurePackages as $insecurePackage) {
                    $this->io()->caution("Package " . $package['name'] . " have a security update, please update to an safe version.");
                    $this->componentCheckInsecureFailed = true;
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
    protected function componentOutdated($modules, $packages)
    {
        foreach ($packages as $package) {
            $projectPackages[] = $package['name'];
        }
        
        $collection = $this->collectionBuilder();
        $collection->taskExecStack()
            ->exec('composer outdated --direct --format=json')
            ->printOutput(false)
            ->storeState('outdated');
        $result = $collection->run();
        $outdatedPackages = ((array) $result['outdated']);
        if (!empty($outdatedPackages)) {
            $decoding = json_decode($outdatedPackages[0]);
            foreach ($decoding->installed as $installed) {
                if (!array_key_exists('latest', $installed)) {
                    $this->io()->error("Package $installed->name does not provide information about last version.");
                } else {
                    $this->io()->note("Package $installed->name with version installed $installed->version is outdated, please update to last version - $installed->latest");
                    $this->componentCheckOutdatedFailed = true;
                }
            }
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
        $content = '';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($basicAuth !== '') {
            $header = ['Authorization: Basic ' . $basicAuth];
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
            return false;
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
        if (!($token = self::getQaSessionToken())) {
            return false;
        }
        $ch = curl_init(getenv('QA_WEBSITE_URL') . '/node?_format=hal_json');
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
     * Note: The project configuration should be updated.
     *
     * @command toolkit:d9-compatibility
     *
     */
    public function d9Compatibility()
    {
        // Build task collection.
        $collection = $this->collectionBuilder();

        // Check if 'upgrade_status' module is already on the project.
        $checkPackage = $this->taskExecStack()
            ->silent(true)
            ->exec('composer show drupal/upgrade_status -q')
            ->stopOnFail()
            ->run();
        // The project already requires this package.
        $this->say("Note: The project configuration should be updated before running this command.");

        if ($checkPackage->wasSuccessful()) {
            $this->say("The module 'upgrade_status' already makes part of the project.");

            if (file_exists('config/sync/core.extension.yml')) {
                $parseConfigFile = Yaml::parseFile('config/sync/core.extension.yml');
                // If it's not enable, enable, analise and remove.
                if (!isset($parseConfigFile['module']['upgrade_status'])) {
                    $collection->taskExecStack()
                        ->silent(true)
                        ->exec('drush en upgrade_status');
                    // Analise all packages/projects (contrib and custom).
                    $collection->taskExecStack()
                        ->exec('drush upgrade_status:analyze --all');
                    // Uninstall module after analisys.
                    $collection->taskExecStack()
                        ->silent(true)
                        ->exec('drush pm:uninstall upgrade_status');
                } else {
                    // Module already installed - just perform analisys.
                    $collection->taskExecStack()
                        ->exec('drush upgrade_status:analyze --all');
                }
            }
            $collection->run();
        } else {
            // If the project don't require this package
            // perform the following actions:
            // Install and enable package.
            // Analise.
            // Uninstall and remove package.
            $this->say("'Package drupal/upgrade_status not found' - Installing required package");
            $collection->taskComposerRequire()
                ->silent(true)
                ->dependency('drupal/upgrade_status', '^2.0')
                ->dev();
            $collection->taskExecStack()
                ->silent(true)
                ->exec('drush en upgrade_status');
            $collection->taskExecStack()
                ->exec('drush upgrade_status:analyze --all');
            $collection->taskExecStack()
                ->silent(true)
                ->exec('drush pm:uninstall upgrade_status');
            $collection->taskExecStack()
                ->silent(true)
                ->exec('composer remove drupal/upgrade_status --dev');
            $collection->run();
        }
        return 0;
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
        $this->skipOutdated = true;
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
        $from = $this->getConfig()->get('toolkit.test.behat.from');
        $blackfire_dir = __DIR__ . '/../../../resources/Blackfire';
        $parseBehatYml = Yaml::parseFile($from);
        if (isset($parseBehatYml['blackfire'])) {
            $this->say('Blackfire profile was found, skipping.');
        } else {
            // Append the Blackfire profile to the behat.yml file.
            $this->taskWriteToFile($from)->append(true)
                ->text(file_get_contents("$blackfire_dir/blackfire.behat.yml"))
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
}
