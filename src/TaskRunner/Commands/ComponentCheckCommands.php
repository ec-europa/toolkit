<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class ComponentCheckCommands extends AbstractCommands
{
    protected bool $commandFailed = false;
    protected bool $mandatoryFailed = false;
    protected bool $recommendedFailed = false;
    protected bool $insecureFailed = false;
    protected bool $outdatedFailed = false;
    protected bool $abandonedFailed = false;
    protected bool $devVersionFailed = false;
    protected bool $devCompRequireFailed = false;
    protected bool $drushRequireFailed = false;
    protected bool $skipOutdated = false;
    protected bool $skipAbandoned = false;
    protected bool $skipInsecure = false;
    protected bool $skipRecommended = true;
    protected int $recommendedFailedCount = 0;

    /**
     * Check composer.json for components that are not whitelisted/blacklisted.
     *
     * @command toolkit:component-check
     *
     * @option endpoint     (Deprecated) Specify an endpoint to use.
     * @option test-command If set the command will load test packages.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function componentCheck(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_OPTIONAL,
        'test-command' => false,
    ])
    {
        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        if (empty($basicAuth = Website::basicAuth())) {
            return 1;
        }

        $commitTokens = ToolCommands::getCommitTokens();
        if (isset($commitTokens['skipOutdated']) || !$this->getConfig()->get('toolkit.components.outdated.check')) {
            $this->skipOutdated = true;
        }
        if (isset($commitTokens['skipInsecure'])) {
            $this->skipInsecure = true;
        }

        $composerLock = file_exists('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;
        if (!isset($composerLock['packages'])) {
            $io->error('No packages found in the composer.lock file.');
            return 1;
        }

        $status = 0;
        $endpoint = Website::url();
        $result = Website::get($endpoint . '/api/v1/package-reviews?version=8.x', $basicAuth);
        $data = json_decode($result, true);
        $modules = array_filter(array_combine(array_column($data, 'name'), $data));

        // To test this command execute it with the --test-command option:
        // ./vendor/bin/run toolkit:component-check --test-command --endpoint="https://webgate.ec.europa.eu/fpfis/qa"
        // Then we provide an array in the packages that fails on each type of validation.
        if ($options['test-command']) {
            $composerLock['packages'] = $this->testPackages();
        }

        // Execute all checks.
        $checks = [
            'Mandatory',
            'Recommended',
            'Insecure',
            'Outdated',
            'Abandoned',
        ];
        foreach ($checks as $check) {
            $io->title("Checking $check components.");
            $fct = "component$check";
            $this->{$fct}($modules, $composerLock['packages']);
            $io->newLine();
        }

        // Get vendor list.
        $dataTkReqsEndpoint = Website::requirements();
        $vendorList = $dataTkReqsEndpoint['vendor_list'] ?? [];

        $io->title('Checking evaluation status components.');
        // Proceed with 'blocker' option. Loop over the packages.
        foreach ($composerLock['packages'] as $package) {
            // Check if vendor belongs to the monitored vendor list.
            if (in_array(explode('/', $package['name'])['0'], $vendorList)) {
                $this->validateComponent($package, $modules);
            }
        }
        if ($this->commandFailed === false) {
            $this->say('Evaluation module check passed.');
        }
        $io->newLine();

        $io->title('Checking dev components.');
        foreach ($composerLock['packages'] as $package) {
            $typeBypass = in_array($package['type'], [
                'drupal-custom-module',
                'drupal-custom-theme',
                'drupal-custom-profile',
            ]);
            if (!$typeBypass && preg_match('[^dev\-|\-dev$]', $package['version'])) {
                $this->devVersionFailed = true;
                $this->writeln("Package {$package['name']}:{$package['version']} cannot be used in dev version.");
            }
        }
        if (!$this->devVersionFailed) {
            $this->say('Dev components check passed.');
        }
        $io->newLine();

        $io->title('Checking dev components in require section.');
        $devPackages = array_filter(
            array_column($modules, 'dev_component', 'name'),
            function ($value) {
                return $value == 'true';
            }
        );
        foreach ($devPackages as $packageName => $package) {
            if (ToolCommands::getPackagePropertyFromComposer($packageName, 'version', 'packages')) {
                $this->devCompRequireFailed = true;
                $io->warning("Package $packageName cannot be used on require section, must be on require-dev section.");
            }
        }
        if (!$this->devCompRequireFailed) {
            $this->say('Dev components in require section check passed');
        }
        $io->newLine();

        $io->title('Checking require section for Drush.');
        if (ToolCommands::getPackagePropertyFromComposer('drush/drush', 'version', 'packages-dev')) {
            $this->drushRequireFailed = true;
            $io->warning("Package 'drush/drush' cannot be used in require-dev, must be on require section.");
        }

        if (!$this->drushRequireFailed) {
            if (ToolCommands::getPackagePropertyFromComposer('drush/drush', 'version', 'packages')) {
                $this->say('Drush require section check passed.');
            }
        }
        $io->newLine();

        $this->printComponentResults($io);

        // If the validation fail, return according to the blocker.
        if (
            $this->commandFailed ||
            $this->mandatoryFailed ||
            (!$this->skipRecommended && $this->recommendedFailed) ||
            $this->devVersionFailed ||
            $this->devCompRequireFailed ||
            $this->drushRequireFailed ||
            (!$this->skipOutdated && $this->outdatedFailed) ||
            (!$this->skipAbandoned && $this->abandonedFailed) ||
            (!$this->skipInsecure && $this->insecureFailed)
        ) {
            $io->error([
                'Failed the components check, please verify the report and update the project.',
                'See the list of packages at',
                'https://webgate.ec.europa.eu/fpfis/qa/package-reviews.',
            ]);
            $status = 1;
        }

        // Give feedback if no problems found.
        if (!$status) {
            $io->success('Components checked, nothing to report.');
        } else {
            $io->note([
                'It is possible to bypass the insecure and outdated check:',
                '- Insecure check:',
                '   - by providing a token in the commit message: [SKIP-INSECURE]',
                '- Outdated check:',
                '   - by providing a token in the commit message: [SKIP-OUTDATED]',
                '   - Or, update the configuration in the runner.yml.dist as shown below: ',
                '        toolkit:',
                '          components:',
                '            outdated:',
                '              check: false',
            ]);
        }

        return $status;
    }

    /**
     * Print the component check results.
     */
    protected function printComponentResults(ConsoleIO $io)
    {
        $io->title('Results:');

        $skipInsecure = ($this->skipInsecure) ? ' (Skipping)' : '';
        $skipOutdated = ($this->skipOutdated) ? ' (Skipping)' : '';
        $skipAbandoned = ($this->skipAbandoned) ? ' (Skipping)' : '';

        $io->definitionList(
            ['Mandatory module check' => $this->getFailedOrPassed($this->mandatoryFailed)],
            ['Recommended module check' => $this->recommendedFailed ? $this->getRecommendedWarningMessage() : 'passed'],
            ['Insecure module check' => $this->getFailedOrPassed($this->insecureFailed) . $skipInsecure],
            ['Outdated module check' => $this->getFailedOrPassed($this->outdatedFailed) . $skipOutdated],
            ['Abandoned module check' => $this->getFailedOrPassed($this->abandonedFailed) . $skipAbandoned],
            ['Dev module check' => $this->getFailedOrPassed($this->devVersionFailed)],
            ['Evaluation module check' => $this->getFailedOrPassed($this->commandFailed)],
            ['Dev module in require-dev check' => $this->getFailedOrPassed($this->devCompRequireFailed)],
            ['Drush require section check' => $this->getFailedOrPassed($this->drushRequireFailed)],
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
    protected function validateComponent(array $package, array $modules)
    {
        // Only validate module components for this time.
        if (!isset($package['type']) || $package['type'] !== 'drupal-module') {
            return;
        }
        $config = $this->getConfig();
        $packageName = $package['name'];
        $hasBeenQaEd = isset($modules[$packageName]);
        $wasRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] !== '0';
        $wasNotRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] === '0';
        $packageVersion = isset($package['extra']['drupal']['version']) ? explode('+', str_replace('8.x-', '', $package['extra']['drupal']['version']))[0] : $package['version'];
        $allowedProjectTypes = !empty($modules[$packageName]['allowed_project_types']) ? $modules[$packageName]['allowed_project_types'] : '';
        $allowedProfiles = !empty($modules[$packageName]['allowed_profiles']) ? $modules[$packageName]['allowed_profiles'] : '';

        // Exclude invalid.
        $packageVersion = in_array($packageVersion, $config->get('toolkit.invalid-versions')) ? $package['version'] : $packageVersion;

        // If module was not reviewed yet.
        if (!$hasBeenQaEd) {
            $this->writeln("Package $packageName:$packageVersion has not been reviewed by QA.");
            $this->commandFailed = true;
        }

        // If module was rejected.
        if ($hasBeenQaEd && $wasRejected) {
            $projectId = $config->get('toolkit.project_id');
            // Check if the module is allowed for this project id.
            $allowedInProject = in_array($projectId, array_map('trim', explode(',', $modules[$packageName]['restricted_use'])));

            // Check if the module is allowed for this type of project.
            if (!$allowedInProject && !empty($allowedProjectTypes)) {
                $allowedProjectTypes = array_map('trim', explode(',', $allowedProjectTypes));
                // Load the project from the website.
                $project = Website::projectInformation($projectId);
                if (in_array($project['type'], $allowedProjectTypes)) {
                    $allowedInProject = true;
                }
            }

            // Check if the module is allowed for this profile.
            if (!$allowedInProject && !empty($allowedProfiles)) {
                $allowedProfiles = array_map('trim', explode(',', $allowedProfiles));
                // Load the project from the website.
                $project = Website::projectInformation($projectId);
                if (in_array($project['profile'], $allowedProfiles)) {
                    $allowedInProject = true;
                }
            }

            // If module was not allowed in project.
            if (!$allowedInProject) {
                $this->writeln("The use of $packageName:$packageVersion is {$modules[$packageName]['status']}. Contact QA Team.");
                $this->commandFailed = true;
            }
        }

        if ($wasNotRejected) {
            $constraints = [ 'whitelist' => false, 'blacklist' => true ];
            foreach ($constraints as $constraint => $result) {
                $constraintValue = !empty($modules[$packageName][$constraint]) ? $modules[$packageName][$constraint] : null;
                if (!is_null($constraintValue) && Semver::satisfies($packageVersion, $constraintValue) === $result) {
                    $this->writeln("Package $packageName:$packageVersion does not meet the $constraint version constraint: $constraintValue.");
                    $this->commandFailed = true;
                }
            }
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @param array $modules The modules list.
     *
     * @throws \Robo\Exception\TaskException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function componentMandatory(array $modules)
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
                $this->writeln("Package $notPresent is mandatory$date and is not present on the project.");

                $this->mandatoryFailed = true;
            }
        }
        if (!$this->mandatoryFailed) {
            $this->say('Mandatory components check passed.');
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @param array $modules The modules list.
     * @param array $packages The packages to validate.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function componentRecommended(array $modules, array $packages)
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
                $this->writeln("Package $notPresent is recommended$date but is not present on the project.");
                $this->recommendedFailed = true;
            }

            $this->say("See the list of recommended packages at https://webgate.ec.europa.eu/fpfis/qa/requirements.");
            $this->recommendedFailedCount = count($diffRecommended);
        }

        if ($this->skipRecommended) {
            $this->say('This step is in reporting mode, skipping.');
        }
    }

    /**
     * Helper function to check component's review information.
     *
     * @param array $modules The modules list.
     *
     * @throws \Robo\Exception\TaskException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function componentInsecure(array $modules)
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
            $msg = "Package $name has a security update, please update to a safe version.";
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
            $this->insecureFailed = true;
        }
        if (!empty($messages)) {
            $this->writeln($messages);
        }

        $fullSkip = getenv('QA_SKIP_INSECURE') !== false && getenv('QA_SKIP_INSECURE');
        // Forcing skip due to issues with the security advisor date detection.
        if ($fullSkip) {
            $this->say('Globally skipping security check for components.');
            $this->insecureFailed = false;
        } elseif (!$this->insecureFailed) {
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

                    // Exclude abandoned packages.
                    if ($outdatedPackage['abandoned'] == FALSE) {
                        if (!array_key_exists('latest', $outdatedPackage)) {
                            $this->writeln("Package {$outdatedPackage['name']} does not provide information about last version.");
                        } elseif (array_key_exists('warning', $outdatedPackage)) {
                            $this->writeln($outdatedPackage['warning']);
                            $this->outdatedFailed = true;
                        } else {
                            $this->writeln("Package {$outdatedPackage['name']} with version installed {$outdatedPackage["version"]} is outdated, please update to last version - {$outdatedPackage['latest']}");
                            $this->outdatedFailed = true;
                        }
                    }
                }
            }
        }

        if (!$this->outdatedFailed) {
            $this->say('Outdated components check passed.');
        }
    }

    /**
     * Helper function to check component's review information.
     *
     */
    protected function componentAbandoned()
    {
        $result = $this->taskExec('composer outdated --direct --minor-only --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();

        $outdatedPackages = json_decode($result, true);

        if (!empty($outdatedPackages['installed'])) {
            if (is_array($outdatedPackages)) {
                foreach ($outdatedPackages['installed'] as $outdatedPackage) {

                    // Only show abandoned packages.
                    if ($outdatedPackage['abandoned'] != FALSE) {
                        $this->writeln($outdatedPackage['warning']);
                        $this->abandonedFailed = true;
                    }
                }
            }
        }

        if (!$this->abandonedFailed) {
            $this->say('Abandoned components check passed.');
        }
    }

    /**
     * Call release history of d.org to confirm security alert.
     *
     * @param string $package
     *   The package to check.
     * @param string $version
     *   The version to check.
     * @param string $core
     *   The package core version.
     *
     * @return array|int
     *   Array with package info from d.org, 1
     *   if no release history found.
     */
    protected function getPackageDetails(string $package, string $version, string $core)
    {
        $name = explode('/', $package)[1];
        // Drupal core is an exception, we should use '/drupal/current'.
        if ($package === 'drupal/core') {
            $url = 'https://updates.drupal.org/release-history/drupal/current';
        } else {
            $url = 'https://updates.drupal.org/release-history/' . $name . '/' . $core;
        }

        $releaseHistory = $fullReleaseHistory = [];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type' => 'application/hal+json']);
        $result = curl_exec($curl);

        if ($result !== false) {
            $fullReleaseHistory[$name] = simplexml_load_string($result);
            $terms = [];
            foreach ($fullReleaseHistory[$name]->releases as $release) {
                foreach ($release as $releaseItem) {
                    $versionTmp = str_replace($core . '-', '', (string) $releaseItem->version);

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

        $this->say('No release history found.');
        return 1;
    }

    /**
     * Returns a list of packages to test.
     *
     * @return array
     *   An array with packages to test.
     */
    private function testPackages()
    {
        return [
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

    /**
     * Returns the recommended components warning message.
     */
    private function getRecommendedWarningMessage(): string
    {
        return $this->recommendedFailedCount . ($this->recommendedFailedCount > 1 ? ' warnings' : ' warning');
    }

    /**
     * If given bool is TRUE 'failed' is return, otherwise 'passed'.
     *
     * @param bool $value
     *   The value to check.
     */
    private function getFailedOrPassed(bool $value): string
    {
        return $value ? 'failed' : 'passed';
    }

}
