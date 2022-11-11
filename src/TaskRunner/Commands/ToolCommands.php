<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\Toolkit;
use OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\loadTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Symfony\Component\Console\Helper\Table;
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
     *
     * @deprecated
     */
    public function displayNotifications(array $options = [
        'endpoint' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $this->output->writeln('<comment>This command is deprecated and will be removed!</comment>');
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
        if (empty($basicAuth = $this->getQaApiBasicAuth())) {
            return 1;
        }

        $this->componentCheckFailed = false;
        $this->componentCheckMandatoryFailed = false;
        $this->componentCheckRecommendedFailed = false;
        $this->componentCheckInsecureFailed = false;
        $this->componentCheckOutdatedFailed = false;
        $this->componentCheckDevVersionFailed = false;
        $this->componentCheckDevCompRequireFailed = false;
        $this->componentCheckDrushRequireFailed = false;

        $this->checkCommitMessage();

        $endpoint = Toolkit::getQaWebsiteUrl();
        if (!empty($options['endpoint'])) {
            $endpoint = $options['endpoint'];
        }
        $composerLock = file_exists('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;

        if (!isset($composerLock['packages'])) {
            $this->io()->error('No packages found in the composer.lock file.');
            return 1;
        }

        $status = 0;
        $result = self::getQaEndpointContent($endpoint . '/api/v1/package-reviews?version=8.x', $basicAuth);
        $data = json_decode($result, true);
        $modules = (array) array_filter(array_combine(array_column($data, 'name'), $data));

        // To test this command execute it with the --test-command option:
        // ./vendor/bin/run toolkit:component-check --test-command --endpoint="https://webgate.ec.europa.eu/fpfis/qa"
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
            $this->io()->newLine();
        }

        // Get vendor list from 'api/v1/toolkit-requirements' endpoint.
        $tkReqsEndpoint = $endpoint . '/api/v1/toolkit-requirements';
        if (empty($basicAuth = $this->getQaApiBasicAuth())) {
            return 1;
        }
        $resultTkReqsEndpoint = self::getQaEndpointContent($tkReqsEndpoint, $basicAuth);
        $dataTkReqsEndpoint = json_decode($resultTkReqsEndpoint, true);
        $vendorList = $dataTkReqsEndpoint['vendor_list'] ?? [];

        $this->io()->title('Checking evaluation status components.');
        // Proceed with 'blocker' option. Loop over the packages.
        foreach ($composerLock['packages'] as $package) {
            // Check if vendor belongs to the monitorised vendor list.
            if (in_array(explode('/', $package['name'])['0'], $vendorList)) {
                $this->validateComponent($package, $modules);
            }
        }
        if ($this->componentCheckFailed == false) {
            $this->say("Evaluation module check passed.");
        }
        $this->io()->newLine();

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
        $this->io()->newLine();

        $this->io()->title('Checking dev components in require section.');
        $devPackages = array_filter(
            array_column($modules, 'dev_component', 'name'),
            function ($value) {
                return $value == 'true';
            }
        );
        foreach ($devPackages as $packageName => $package) {
            if (ToolCommands::getPackagePropertyFromComposer($packageName, 'version', 'packages')) {
                $this->componentCheckDevCompRequireFailed = true;
                $this->io()->warning("Package $packageName cannot be used on require section, must be on require-dev section.");
            }
        }
        if (!$this->componentCheckDevCompRequireFailed) {
            $this->say('Dev components in require section check passed');
        }
        $this->io()->newLine();

        $this->io()->title('Checking require section for Drush.');
        if (ToolCommands::getPackagePropertyFromComposer('drush/drush', 'version', 'packages-dev')) {
            $this->componentCheckDrushRequireFailed = true;
            $this->io()->warning("Package 'drush/drush' cannot be used in require-dev, must be on require section.");
        }

        if (!$this->componentCheckDrushRequireFailed) {
            if (ToolCommands::getPackagePropertyFromComposer('drush/drush', 'version', 'packages')) {
                $this->say('Drush require section check passed.');
            }
        }
        $this->io()->newLine();

        $this->printComponentResults();

        // If the validation fail, return according to the blocker.
        if (
            $this->componentCheckFailed ||
            $this->componentCheckMandatoryFailed ||
            $this->componentCheckRecommendedFailed ||
            $this->componentCheckDevVersionFailed ||
            $this->componentCheckDevCompRequireFailed ||
            $this->componentCheckDrushRequireFailed ||
            (!$this->skipInsecure && $this->componentCheckInsecureFailed)
        ) {
            $msg = [
                'Failed the components check, please verify the report and update the project.',
                'See the list of packages at https://webgate.ec.europa.eu/fpfis/qa/package-reviews.',
            ];
            $this->io()->error($msg);
            $status = 1;
        }

        // Give feedback if no problems found.
        if (!$status) {
            $this->io()->success('Components checked, nothing to report.');
        } else {
            $this->io()->note([
                'NOTE: It is possible to bypass the insecure and outdated check by providing a token in the commit message.',
                'The available tokens are:',
                '    - [SKIP-OUTDATED]',
                '    - [SKIP-INSECURE]',
            ]);
        }

        return $status;
    }

    /**
     * Helper function to validate the component.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function printComponentResults()
    {
        $this->io()->title('Results:');

        $skipInsecure = ($this->skipInsecure) ? ' (Skipping)' : '';
        $skipOutdated = ($this->skipOutdated) ? '' : ' (Skipping)';

        $this->io()->definitionList(
            ['Mandatory module check ' => $this->componentCheckMandatoryFailed ? 'failed' : 'passed'],
            ['Recommended module check ' => $this->componentCheckRecommendedFailed ? 'failed' : 'passed' . ' (report only)'],
            ['Insecure module check ' => $this->componentCheckInsecureFailed ? 'failed' : 'passed' . $skipInsecure],
            ['Outdated module check ' => $this->componentCheckOutdatedFailed ? 'failed' : 'passed' . $skipOutdated],
            ['Dev module check ' => $this->componentCheckDevVersionFailed ? 'failed' : 'passed'],
            ['Evaluation module check ' => $this->componentCheckFailed ? 'failed' : 'passed'],
            ['Dev module in require-dev check ' => $this->componentCheckDevCompRequireFailed ? 'failed' : 'passed'],
            ['Drush require section check ' => $this->componentCheckDrushRequireFailed ? 'failed' : 'passed'],
        );
    }

    /**
     * Helper function to validate the component.
     *
     * @param array $package The package to validate.
     * @param array $modules The modules list.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function validateComponent($package, $modules)
    {
        // Only validate module components for this time.
        if (!isset($package['type']) || $package['type'] !== 'drupal-module') {
            return;
        }
        $packageName = $package['name'];
        $hasBeenQaEd = isset($modules[$packageName]);
        $wasRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] !== '0';
        $wasNotRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] === '0';
        $packageVersion = isset($package['extra']['drupal']['version']) ? explode('+', str_replace('8.x-', '', $package['extra']['drupal']['version']))[0] : $package['version'];
        $allowedProjectTypes = !empty($modules[$packageName]['allowed_project_types']) ? $modules[$packageName]['allowed_project_types'] : '';
        $allowedProfiles = !empty($modules[$packageName]['allowed_profiles']) ? $modules[$packageName]['allowed_profiles'] : '';

        // Exclude invalid.
        $packageVersion = in_array($packageVersion, $this->getConfig()->get('toolkit.invalid-versions')) ?  $package['version'] : $packageVersion;

        // If module was not reviewed yet.
        if (!$hasBeenQaEd) {
            $this->say("Package $packageName:$packageVersion has not been reviewed by QA.");
            $this->componentCheckFailed = true;
        }

        // If module was rejected.
        if ($hasBeenQaEd && $wasRejected) {
            $projectId = $this->getConfig()->get('toolkit.project_id');
            // Check if the module is allowed for this project id.
            $allowedInProject = in_array($projectId, array_map('trim', explode(',', $modules[$packageName]['restricted_use'])));

            // Check if the module is allowed for this type of project.
            if (!$allowedInProject && !empty($allowedProjectTypes)) {
                $allowedProjectTypes = array_map('trim', explode(',', $allowedProjectTypes));
                // Load the project from the website.
                $project = $this->getQaProjectInformation($projectId);
                if (in_array($project['type'], $allowedProjectTypes)) {
                    $allowedInProject = true;
                }
            }

            // Check if the module is allowed for this profile.
            if (!$allowedInProject && !empty($allowedProfiles)) {
                $allowedProfiles = array_map('trim', explode(',', $allowedProfiles));
                // Load the project from the website.
                $project = $this->getQaProjectInformation($projectId);
                if (in_array($project['profile'], $allowedProfiles)) {
                    $allowedInProject = true;
                }
            }

            // If module was not allowed in project.
            if (!$allowedInProject) {
                $this->say("The use of $packageName:$packageVersion is {$modules[$packageName]['status']}. Contact QA Team.");
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
                    echo "Package $packageName:$packageVersion does not meet the $constraint version constraint: $constraintValue." . PHP_EOL;
                    $this->componentCheckFailed = true;
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
     * @param array $modules The modules list.
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function componentMandatory($modules)
    {
        $enabledPackages = $mandatoryPackages = [];
        $drushBin = $this->getBin('drush');
        // Check if the website is installed.
        $result = $this->taskExec($drushBin . ' status --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $status = json_decode($result, true);
        if (empty($status['db-name'])) {
            $config_file = $this->getConfig()->get('toolkit.clean.config_file');
            $this->say("Website not installed, using $config_file file.");
            if (file_exists($config_file)) {
                $config = Yaml::parseFile($config_file);
                $enabledPackages = array_keys(array_merge(
                    $config['module'] ?? [],
                    $config['theme'] ?? []
                ));
            } else {
                $this->say("Config file not found at $config_file.");
            }
        } else {
            // Get enabled packages.
            $result = $this->taskExec($drushBin . ' pm-list --fields=status --format=json')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run()->getMessage();
            $projPackages = json_decode($result, true);
            if (!empty($projPackages)) {
                $enabledPackages = array_keys(array_filter($projPackages, function ($item) {
                    return $item['status'] === 'Enabled';
                }));
            }
        }

        // Get mandatory packages.
        if (!empty($modules)) {
            $mandatoryPackages = array_values(array_filter($modules, function ($item) {
                return $item['mandatory'] === '1';
            }));
        }

        $diffMandatory = array_diff(array_column($mandatoryPackages, 'machine_name'), $enabledPackages);
        if (!empty($diffMandatory)) {
            foreach ($diffMandatory as $notPresent) {
                $index = array_search($notPresent, array_column($mandatoryPackages, 'machine_name'));
                $date = !empty($mandatoryPackages[$index]['mandatory_date']) ? ' (since ' . $mandatoryPackages[$index]['mandatory_date'] . ')' : '';
                echo "Package $notPresent is mandatory$date and is not present on the project." . PHP_EOL;
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
     * @param array $modules The modules list.
     * @param array $packages The packages to validate.
     */
    protected function componentRecommended($modules, $packages)
    {
        $recommendedPackages = [];
        // Get project packages.
        $projectPackages = array_column($packages, 'name');
        // Get recommended packages.
        if (!empty($modules)) {
            $recommendedPackages = array_values(array_filter($modules, function ($item) {
                return strtolower($item['usage']) === 'recommended';
            }));
        }

        $diffRecommended = array_diff(array_column($recommendedPackages, 'name'), $projectPackages);
        if (!empty($diffRecommended)) {
            foreach ($diffRecommended as $notPresent) {
                $index = array_search($notPresent, array_column($recommendedPackages, 'name'));
                $date = !empty($recommendedPackages[$index]['mandatory_date']) ? ' (and will be mandatory at ' . $recommendedPackages[$index]['mandatory_date'] . ')' : '';
                echo "Package $notPresent is recommended$date but is not present on the project." . PHP_EOL;
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
     * @param array $modules The modules list.
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function componentInsecure($modules)
    {
        $packages = [];
        $drush_result = $this->taskExec($this->getBin('drush') . ' pm:security --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $drush_result = trim($drush_result);
        if (!empty($drush_result) && $drush_result !== '[]') {
            $data = json_decode($drush_result, true);
            if (!empty($data) && is_array($data)) {
                $packages = $data;
            }
        }

        $sc_result = $this->taskExec($this->getBin('security-checker') . ' security:check --no-dev --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $sc_result = trim($sc_result);
        if (!empty($sc_result) && $sc_result !== '[]') {
            $data = json_decode($sc_result, true);
            if (!empty($data) && is_array($data)) {
                $packages = array_merge($packages, $data);
            }
        }

        $messages = [];
        foreach ($packages as $name => $package) {
            $msg = "Package $name have a security update, please update to a safe version.";
            if (!empty($modules[$name]['secure'])) {
                if (Semver::satisfies($package['version'], $modules[$name]['secure'])) {
                    $messages[] = "$msg (Version marked as secure)";
                    continue;
                }
            }
            $historyTerms = $this->getPackageDetails($name, $package['version'], '8.x');
            if (!empty($historyTerms) && (empty($historyTerms['terms']) || !in_array('insecure', $historyTerms['terms']))) {
                $messages[] = "$msg (Confirmation failed, ignored)";
                continue;
            }

            $messages[] = $msg;
            $this->componentCheckInsecureFailed = true;
        }
        if (!empty($messages)) {
            $this->writeln($messages);
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
                        echo "Package " . $outdatedPackage['name'] . " does not provide information about last version." . PHP_EOL;
                    } elseif (array_key_exists('warning', $outdatedPackage)) {
                        echo $outdatedPackage['warning'] . PHP_EOL;
                        $this->componentCheckOutdatedFailed = true;
                    } else {
                        echo "Package " . $outdatedPackage['name'] . " with version installed " . $outdatedPackage["version"] . " is outdated, please update to last version - " . $outdatedPackage["latest"] . PHP_EOL;
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
     *   The endpoint content, or empty string if no session is generated.
     *
     * @throws \Exception
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
                "Authorization: Basic $basicAuth",
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
        if (!empty($GLOBALS['session_token'])) {
            return $GLOBALS['session_token'];
        }
        $url = Toolkit::getQaWebsiteUrl();
        $options = [
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_ENCODING       => '',     // handle compressed
            CURLOPT_USERAGENT      => 'Quality Assurance pipeline', // name of client
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT        => 120,    // time-out on response
        ];
        $ch = curl_init("$url/session/token");
        curl_setopt_array($ch, $options);
        $token = curl_exec($ch);
        curl_close($ch);
        $GLOBALS['session_token'] = $token;
        return $token;
    }

    /**
     * Helper to send a payload to the QA Website.
     *
     * @param array $fields
     *   Data to send.
     * @param string $auth
     *   The Basic auth.
     *
     * @return string
     *   The endpoint response code, or empty string if no session is generated.
     *
     * @throws \Exception
     */
    public static function postQaContent(array $fields, string $auth): string
    {
        $url = Toolkit::getQaWebsiteUrl();
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
            "Authorization: Basic $auth",
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return (string) $code;
    }

    /**
     * Check project compatibility for Drupal 9/10 upgrade.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @command toolkit:drupal-upgrade-status
     *
     * @aliases tdus
     */
    public function drupalUpgradeStatus(): int
    {

        $this->checkCommitMessage();

        if (!$this->skipDus) {
            return 0;
        }

        // Prepare project.
        $this->say("Preparing the project to run upgrade_status.");
        $drushBin = $this->getBin('drush');
        $collection = $this->collectionBuilder();
        // Require 'drupal/upgrade_status' if does not exist on the project.
        if (self::getPackagePropertyFromComposer('drupal/upgrade_status') == false) {
            $collection->taskComposerRequire()
            ->dependency('drupal/upgrade_status', '^3')
            ->dev()
            ->run();
        }
        // Require 'drupal/core-dev' if does not exist on the project.
        if (self::getPackagePropertyFromComposer('drupal/core-dev') == false) {
            $collection->taskComposerRequire()
            ->dependency('drupal/core-dev')
            ->dev()
            ->run();
        }

        // Build collection.
        $collection = $this->collectionBuilder();
        $collection->taskExecStack()
            ->exec($drushBin . ' en upgrade_status -y')
            ->run();

        // Perform the default analysis to all contrib and custom components.
        $result = $collection->taskExecStack()
            ->exec($drushBin . ' us-a --all')
            ->printOutput(false)
            ->storeState('insecure')
            ->silent(true)
            ->run()
            ->getMessage();

        // Check flagged results.
        $qaCompatibilityResult = 0;
        if (is_string($result)) {
            $flags = [
                'Check manually',
                'Fix now',
            ];
            foreach ($flags as $flag) {
                if (strpos($result, $flag) !== false) {
                    $qaCompatibilityResult = 1;
                }
            }
        }
        echo $result . PHP_EOL;
        $drupal_version = self::getPackagePropertyFromComposer('drupal/core');
        if ($qaCompatibilityResult) {
            $this->say('Looks the project need some attention, please check the report above.');
        } else {
            if (Semver::satisfies($drupal_version, '^8')) {
                $this->say('Congrats, looks like your project is Drupal 9 compatible.');
            }
            if (Semver::satisfies($drupal_version, '^9')) {
                $this->say('Congrats, looks like your project is Drupal 10 compatible.');
            }
        }

        return $qaCompatibilityResult;
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @command toolkit:opts-review
     */
    public function optsReview()
    {
        if (file_exists('.opts.yml')) {
            if (empty($basicAuth = $this->getQaApiBasicAuth())) {
                return 1;
            }
            $project_id = $this->getConfig()->get('toolkit.project_id');
            $url = Toolkit::getQaWebsiteUrl();
            $url .= '/api/v1/project/ec-europa/' . $project_id . '-reference/information/constraints';
            $result = self::getQaEndpointContent($url, $basicAuth);
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
                            $parsedCommand = explode(" ", $command);
                            if (in_array($forbiddenCommand, $parsedCommand)) {
                                $this->say("The command '$command' is not allowed. Please remove it from 'upgrade_commands' section.");
                                $reviewOk = false;
                            }
                        } else {
                            foreach ($command as $env => $subCommand) {
                                $parsedCommand = explode(' ', $subCommand);
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
     * Check the commit message for SKIPPING tokens.
     */
    protected function checkCommitMessage()
    {
        $this->skipOutdated = false;
        $this->skipInsecure = false;
        $this->skipDus = true;

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
                    $this->skipDus = false;
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

        $url = Toolkit::getQaWebsiteUrl();
        if (empty($options['endpoint'])) {
            $options['endpoint'] = $url . '/api/v1/toolkit-requirements';
        }
        $php_check = $toolkit_check = $drupal_check = $endpoint_check = $nextcloud_check = $asda_check = 'FAIL';
        $php_version = $toolkit_version = $drupal_version = '';

        if (empty($basicAuth = $this->getQaApiBasicAuth())) {
            return 1;
        }
        $result = self::getQaEndpointContent($options['endpoint'], $basicAuth);
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
        $this->say("Checking Toolkit version:\n");

        $url = Toolkit::getQaWebsiteUrl();
        $endpoint = $url . '/api/v1/toolkit-requirements';
        if (empty($basicAuth = $this->getQaApiBasicAuth())) {
            return 1;
        }
        $toolkit_version = Toolkit::VERSION;
        $min_version = '';

        $result = self::getQaEndpointContent($endpoint, $basicAuth);
        $min_version = '';

        if (!($composer_version = self::getPackagePropertyFromComposer('ec-europa/toolkit'))) {
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
        $url = Toolkit::getQaWebsiteUrl();
        $endpoint = $url . '/api/v1/toolkit-requirements';
        if (empty($basicAuth = $this->getQaApiBasicAuth())) {
            return 1;
        }
        $result = self::getQaEndpointContent($endpoint, $basicAuth);

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
     * Return the QA API BASIC AUTH from token or from questions.
     *
     * @return string
     *   The Basic auth or empty string if fails.
     */
    public function getQaApiBasicAuth(): string
    {
        if (!empty($GLOBALS['basic_auth'])) {
            return $GLOBALS['basic_auth'];
        }
        $auth = getenv('QA_API_BASIC_AUTH');
        if (empty($auth)) {
            $this->say('Missing env var QA_API_BASIC_AUTH, asking for access.');
            if (empty($user = $this->ask('Please insert your username:'))) {
                $this->writeln('<error>The username cannot be empty!</error>');
                return '';
            }
            if (empty($pass = $this->ask('Please insert your password:', true))) {
                $this->writeln('<error>The password cannot be empty!</error>');
                return '';
            }
            $auth = base64_encode("$user:$pass");
            $this->writeln([
                'Your token has been generated, please add it to your environment variables.',
                '    export QA_API_BASIC_AUTH="' . $auth . '"',
            ]);
            $GLOBALS['basic_auth'] = $auth;
        }

        return $auth;
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
     * @command toolkit:code-review
     *
     * @option phpcs Execute the command toolkit:test-phpcs.
     * @option opts-review Execute the command toolkit:opts-review.
     * @option lint-php Execute the command toolkit:lint-php.
     * @option lint-yaml Execute the command toolkit:lint-yaml.
     */
    public function toolkitCodeReview(array $options = [
        'phpcs' => InputOption::VALUE_NONE,
        'opts-review' => InputOption::VALUE_NONE,
        'lint-php' => InputOption::VALUE_NONE,
        'lint-yaml' => InputOption::VALUE_NONE,
    ])
    {
        // If at least one option is given, use given options, else use all.
        $phpcsResult = $optsReviewResult = $lintPhpResult = $lintYamlResult = [];
        $phpcs = $options['phpcs'] !== InputOption::VALUE_NONE;
        $optsReview = $options['opts-review'] !== InputOption::VALUE_NONE;
        $lintPhp = $options['lint-php'] !== InputOption::VALUE_NONE;
        $lintYaml = $options['lint-yaml'] !== InputOption::VALUE_NONE;
        $exit = 0;

        if ($phpcs || $optsReview || $lintPhp || $lintYaml) {
            // Run given checks.
            $runPhpcs = $phpcs;
            $runOptsReview = $optsReview;
            $runLintPhp = $lintPhp;
            $runLintYaml = $lintYaml;
        } else {
            // Run all checks.
            $runPhpcs = $runOptsReview = $runLintPhp = $runLintYaml = true;
        }
        $run = $this->getBin('run');
        if ($runPhpcs) {
            $code = $this->taskExec($run . ' toolkit:test-phpcs')
                ->run()->getExitCode();
            $phpcsResult = ['PHPcs' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }
        if ($runOptsReview) {
            $code = $this->taskExec($run . ' toolkit:opts-review')
                ->run()->getExitCode();
            $optsReviewResult = ['Opts review' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }
        if ($runLintPhp) {
            $code = $this->taskExec($run . ' toolkit:lint-php')
                ->run()->getExitCode();
            $lintPhpResult = ['Lint PHP' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }
        if ($runLintYaml) {
            $code = $this->taskExec($run . ' toolkit:lint-yaml')
                ->run()->getExitCode();
            $lintYamlResult = ['Lint YAML' => $code > 0 ? 'failed' : 'passed'];
            $exit += $code;
            $this->io()->newLine(2);
        }

        $this->io()->title('Results:');
        $this->io()->definitionList($phpcsResult, $optsReviewResult, $lintPhpResult, $lintYamlResult);

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
     * Returns the Project information from the QA Website.
     *
     * @param $project_id
     *   The project ID to use.
     *
     * @return false|array
     *   An array with the Project information, false if fails.
     *
     * @throws \Exception
     */
    public function getQaProjectInformation($project_id)
    {
        if (!isset($GLOBALS['projects'])) {
            $GLOBALS['projects'] = [];
        }
        if (!empty($GLOBALS['projects'][$project_id])) {
            return $GLOBALS['projects'][$project_id];
        }
        $url = Toolkit::getQaWebsiteUrl();
        $endpoint = "$url/api/v1/project/ec-europa/$project_id-reference/information";
        $project = self::getQaEndpointContent($endpoint, $this->getQaApiBasicAuth());
        $project = json_decode($project, true);
        $project = reset($project);
        if (!empty($project['name']) && $project['name'] === "$project_id-reference") {
            $GLOBALS['projects'][$project_id] = $project;
            return $project;
        }

        return false;
    }

    /**
     * Install packages present in the opts.yml file under extra_pkgs section.
     *
     * @command toolkit:install-dependencies
     *
     * @option print Shows output from apt commands.
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

        $print = $options['print'] !== InputOption::VALUE_NONE;
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
            if (strpos($info, '[installed]') !== false) {
                $data[$package] = 'already installed';
            } elseif (strpos($info, $package) === false) {
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
                if (strpos($info, '[installed]') !== false) {
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
