<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use Dotenv\Dotenv;
use EcEuropa\Toolkit\DrupalReleaseHistory;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Command class for toolkit:component-check
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class ComponentCheckCommands extends AbstractCommands
{
    protected bool $evaluationFailed = false;
    protected bool $mandatoryFailed = false;
    protected bool $recommendedFailed = false;
    protected bool $insecureFailed = false;
    protected bool $outdatedFailed = false;
    protected bool $abandonedFailed = false;
    protected bool $unsupportedFailed = false;
    protected bool $composerFailed = false;
    protected bool $configurationFailed = false;
    protected bool $devCompRequireFailed = false;
    protected bool $skipOutdated = false;
    protected bool $skipAbandoned = false;
    protected bool $skipUnsupported = false;
    protected bool $skipInsecure = false;
    protected bool $skipRecommended = false;
    protected int $recommendedFailedCount = 0;
    protected array $composerOutdated;
    protected $io;
    protected array $composerLock;
    protected array $packageReviews;
    protected bool $forcedUpdateModule = false;
    protected bool $disabledConfigReadonly = false;

    /**
     * Check composer for components that are not whitelisted/blacklisted.
     *
     * @command toolkit:component-check
     *
     * @option endpoint     The endpoint to use to connect to QA Website.
     * @option test-command If set the command will load test packages.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function componentCheck(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
        'test-command' => false,
    ])
    {
        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        if (empty(Website::apiAuth())) {
            return 1;
        }
        $this->io = $io;
        $this->prepareSkips();
        if (!$this->loadComposerLock()) {
            return 1;
        }
        if (!$this->loadWebsitePackages()) {
            return 1;
        }

        // To test this command execute it with the --test-command option:
        // ./vendor/bin/run toolkit:component-check --test-command --endpoint="https://digit-dqa.fpfis.tech.ec.europa.eu"
        // Then we provide an array in the packages that fails on each type of validation.
        if ($options['test-command']) {
            $this->composerLock['packages'] = $this->testPackages();
        }

        // Execute all checks.
        $checks = [
            'Mandatory',
            'Recommended',
            'Insecure',
            'Outdated',
            'Abandoned',
            'Unsupported',
            'Evaluation',
            'Development',
            'Composer',
            'Configuration',
        ];
        foreach ($checks as $check) {
            $io->title("Checking $check components.");
            $fct = "component$check";
            $this->{$fct}($io);
            $io->newLine();
        }

        $this->printComponentResults($io);

        // If the validation fail, return according to the blocker.
        $status = 0;
        if (
            $this->evaluationFailed ||
            $this->mandatoryFailed ||
            $this->devCompRequireFailed ||
            $this->composerFailed ||
            $this->configurationFailed ||
            (!$this->skipRecommended && $this->recommendedFailed) ||
            (!$this->skipOutdated && $this->outdatedFailed) ||
            (!$this->skipAbandoned && $this->abandonedFailed) ||
            (!$this->skipUnsupported && $this->unsupportedFailed) ||
            (!$this->skipInsecure && $this->insecureFailed)
        ) {
            $io->error([
                'Failed the components check, please verify the report and update the project.',
                'See the list of packages at',
                'https://digit-dqa.fpfis.tech.ec.europa.eu/package-reviews.',
            ]);
            $status = 1;
        }

        // Give feedback if no problems found.
        if (!$status) {
            $io->success('Components checked, nothing to report.');
        } else {
            $io->note([
                'It is possible to bypass the insecure, outdated, abandoned and unsupported checks:',
                '- Using commit message to skip Insecure and/or Outdated check:',
                '   - Include in the message: [SKIP-INSECURE] and/or [SKIP-OUTDATED]',
                '',
                '- Using the configuration in the runner.yml.dist as shown below to skip Outdated, Abandoned or Unsupported: ',
                '   toolkit:',
                '     components:',
                '       outdated:',
                '         check: false',
                '       abandoned:',
                '         check: false',
                '       unsupported:',
                '         check: false',
            ]);
        }

        return $status;
    }

    /**
     * Check mandatory components.
     *
     * @command check:mandatory
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function componentMandatory(ConsoleIO $io)
    {
        $this->io = $io;
        if (!$this->loadWebsitePackages()) {
            return 1;
        }
        $enabledModules = $mandatoryPackages = [];
        if (!$this->isWebsiteInstalled()) {
            $configFile = $this->getConfig()->get('toolkit.clean.config_file');
            $this->writeln("Website not installed, using $configFile file.");
            if (file_exists($configFile)) {
                $config = Yaml::parseFile($configFile);
                $enabledModules = array_keys(array_merge($config['module'] ?? [], $config['theme'] ?? []));
            } else {
                $this->writeln("Config file not found at $configFile.");
            }
        } else {
            // Get enabled modules.
            $result = $this->taskExec($this->getBin('drush') . ' pm-list --status=enabled --format=json')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run()->getMessage();
            $enabledModules = array_keys(json_decode($result, true));
        }

        // Get mandatory packages.
        if (!empty($this->packageReviews)) {
            $mandatoryPackages = array_values(array_filter($this->packageReviews, function ($item) {
                return $item['mandatory'] === '1' && $item['type'] === 'drupal-module';
            }));
        }

        $diffMandatory = array_diff(array_column($mandatoryPackages, 'machine_name'), $enabledModules);
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
     * Check recommended components.
     *
     * @command check:recommended
     */
    public function componentRecommended(ConsoleIO $io)
    {
        $this->io = $io;
        if (!$this->loadComposerLock()) {
            return 1;
        }
        $recommendedPackages = [];
        // Get project packages.
        $projectPackages = array_column($this->composerLock['packages'], 'name');
        // Get recommended packages.
        if (!empty($this->packageReviews)) {
            $recommendedPackages = array_values(array_filter($this->packageReviews, function ($item) {
                return !empty($item['usage']) && strtolower($item['usage']) === 'recommended';
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

            $this->say("See the list of recommended packages at\nhttps://digit-dqa.fpfis.tech.ec.europa.eu/requirements.");
            $this->recommendedFailedCount = count($diffRecommended);
        }

        if ($this->skipRecommended) {
            $this->say('This step is in reporting mode, skipping.');
        }
    }

    /**
     * Check insecure components.
     *
     * @command check:insecure
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function componentInsecure(ConsoleIO $io)
    {
        $this->io = $io;
        $this->prepareSkips();
        if (!$this->loadComposerLock()) {
            return 1;
        }
        $packages = [];
        $drupalReleaseHistory = new DrupalReleaseHistory();
        $exec = $this->taskExec('composer audit --no-dev --locked --no-scripts --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run();
        $result = trim($exec->getMessage());
        if (!empty($result) && $result !== '[]') {
            $data = json_decode($result, true);
            if (!empty($data['advisories']) && is_array($data['advisories'])) {
                // Each package might have multiple issues, we take the first.
                foreach ($data['advisories'] as $advisory) {
                    $firstAdvisory = array_shift($advisory);
                    $packageName = $firstAdvisory['packageName'];
                    $packages[$packageName]['title'] = $firstAdvisory['title'];
                    $packages[$packageName]['version'] = ToolCommands::getPackagePropertyFromComposer($packageName);
                }
            }
        }

        $messages = [];
        foreach ($packages as $name => $package) {
            $msg = "Package $name has a security update, please update to a safe version. (" . $package['title'] . ")";
            if (!empty($this->packageReviews[$name]['secure'])) {
                if (Semver::satisfies($package['version'], $this->packageReviews[$name]['secure'])) {
                    $messages[] = "$msg (Version marked as secure)";
                    continue;
                }
            }
            $historyTerms = $drupalReleaseHistory->getPackageDetails($name, $package['version'], '8.x');
            if ($historyTerms === 1) {
                $this->say("No release history found for package $name.");
                continue;
            }
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
     * Check outdated components.
     *
     * @command check:outdated
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function componentOutdated(ConsoleIO $io)
    {
        $this->loadComposerOutdated();
        // Using the option --locked, we must check for the "locked" key.
        if (is_array($this->composerOutdated) && !empty($this->composerOutdated['locked'])) {
            $ignores = $this->getConfig()->get('toolkit.components.outdated.ignores');
            if (!empty($ignores)) {
                $ignores = array_combine(
                    array_column($ignores, 'name'),
                    array_column($ignores, 'version')
                );
            }

            foreach ($this->composerOutdated['locked'] as $package) {
                // Exclude abandoned packages, see $this->componentAbandoned().
                if ($package['abandoned']) {
                    continue;
                }
                // Check for ignores and compare versions.
                if (!empty($ignores) && isset($ignores[$package['name']]) && $package['version'] === $ignores[$package['name']]) {
                    $io->writeln("Package {$package['name']} with version installed {$package['version']} skipped by config.");
                    continue;
                }

                if (!array_key_exists('latest', $package)) {
                    $io->writeln("Package {$package['name']} does not provide information about last version.");
                } elseif (array_key_exists('warning', $package)) {
                    $io->writeln($package['warning']);
                    $this->outdatedFailed = true;
                } else {
                    $io->writeln("Package {$package['name']} with version installed {$package['version']} is outdated, please update to last version - {$package['latest']}");
                    $this->outdatedFailed = true;
                }
            }
        }

        if (!$this->outdatedFailed) {
            $io->say('Outdated components check passed.');
        }
    }

    /**
     * Check abandoned components.
     *
     * @command check:abandoned
     */
    public function componentAbandoned(ConsoleIO $io)
    {
        $this->loadComposerOutdated();
        $packages = $this->composerOutdated['locked'] ?? [];
        if (!empty($packages)) {
            foreach ($packages as $package) {
                // Only show abandoned packages.
                if ($package['abandoned'] != false) {
                    $this->writeln($package['warning']);
                    $this->abandonedFailed = true;
                }
            }
        }
        if (!$this->abandonedFailed) {
            $io->say('Abandoned components check passed.');
        }
    }

    /**
     * Check unsupported components.
     *
     * @command check:unsupported
     */
    public function componentUnsupported(ConsoleIO $io)
    {
        if (!$this->isWebsiteInstalled()) {
            $io->writeln('Website not installed, skipping.');
            return;
        }
        $this->disableConfigReadOnly();
        if (empty($releases = $this->getReleases())) {
            $io->writeln('Failed to get the available releases.');
            return;
        }
        // Filter by unsupported, @see \Drupal\update\UpdateManagerInterface::NOT_SUPPORTED.
        $unsupported = array_filter($releases, function ($item) {
            return $item['status'] === 3;
        });
        if (empty($unsupported)) {
            $io->say('Unsupported components check passed.');
        } else {
            $this->unsupportedFailed = true;
            foreach ($unsupported as $item) {
                if (array_key_exists('recommended', $item)) {
                    $io->writeln(sprintf(
                        "Package %s with version installed %s is not supported. Update to the recommended version %s",
                        $item['name'],
                        $item['existing_version'],
                        $item['recommended']
                    ));
                } else {
                    $io->writeln(sprintf(
                        "Package %s is no longer supported, and is no longer available for download. Disabling everything included by this project is strongly recommended!",
                        $item['name']
                    ));
                }
            }
        }

        if ($this->forcedUpdateModule) {
            $this->_exec($this->getBin('drush') . ' pm:uninstall update -y');
        }
        $this->restoreConfigReadOnly();
    }

    /**
     * Check Evaluation components.
     *
     * @command check:evaluation
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function componentEvaluation(ConsoleIO $io)
    {
        $this->io = $io;
        if (!$this->loadComposerLock()) {
            return 1;
        }
        if (!$this->loadWebsitePackages()) {
            return 1;
        }
        // Get vendor list.
        $dataTkReqsEndpoint = Website::requirements();
        $vendorList = $dataTkReqsEndpoint['vendor_list'] ?? [];

        // Proceed with 'blocker' option. Loop over the packages.
        $groupComponents = [];
        foreach ($this->composerLock['packages'] as $package) {
            // Check if vendor belongs to the monitored vendor list.
            if (in_array(explode('/', $package['name'])['0'], $vendorList)) {
                $validateComponent = $this->validateComponent($package);
                if ($validateComponent) {
                    $groupComponents[$validateComponent['1']][] = $validateComponent['0'];
                }
            }
        }
        foreach ($groupComponents as $groupComponent => $messages) {
            $this->writeln($groupComponent);
            foreach ($messages as $message) {
                $this->writeln($message);
            }
            if ($groupComponent == 'Packages rejected/restricted:') {
                $this->writeln('<options=reverse>In the case you want to use one of the modules listed as restricted, please open a ticket to Quality Assurance indicating the use case for evaluation and more information.</>');
            }
        }
        if ($this->evaluationFailed === false) {
            $this->say('Evaluation module check passed.');
        }
        $this->io->newLine();
    }

    /**
     * Check development components.
     *
     * @command check:development
     */
    public function componentDevelopment(ConsoleIO $io)
    {
        $this->io = $io;
        if (!$this->loadWebsitePackages()) {
            return 1;
        }
        $devPackages = array_filter(
            array_column($this->packageReviews, 'dev_component', 'name'),
            function ($value) {
                return $value == 'true';
            }
        );
        foreach (array_keys($devPackages) as $packageName) {
            if (ToolCommands::getPackagePropertyFromComposer($packageName, 'version', 'packages')) {
                $this->devCompRequireFailed = true;
                $this->io->warning("Package $packageName cannot be used on require section, must be on require-dev section.");
            }
        }
        if (!$this->devCompRequireFailed) {
            $this->say('Development components check passed.');
        }
        $this->io->newLine();
    }

    /**
     * Check project configuration.
     *
     * @command check:configuration
     */
    public function componentConfiguration(ConsoleIO $io)
    {
        $this->io = $io;
        // Forbid deprecated environment variables.
        $this->validateEnvironmentVariables();

        // Dynamic validations.
        $validations = $this->getConfig()->get('toolkit.components.configuration.validations');
        foreach ($validations as $validation) {
            $params = !empty($validation['params']) ? $validation['params'] : [];
            $expectation = !isset($validation['expectation']) ? false : $validation['expectation'];
            if (call_user_func_array($validation['callback'], $params) === $expectation) {
                if (!empty($validation['blocker'])) {
                    $this->io->error($validation['message']);
                    $this->configurationFailed = true;
                } else {
                    $this->io->warning($validation['message']);
                }
            }
        }

        if (!$this->configurationFailed) {
            $this->say('Project configuration check passed.');
        }
        $this->io->newLine();
    }

    /**
     * Check composer packages.
     *
     * @command check:composer
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function componentComposer(ConsoleIO $io)
    {
        $this->io = $io;
        if (!$this->loadComposerLock()) {
            return 1;
        }
        $composerJson = $this->getJson('composer.json');

        // Check packages used in dev version.
        foreach ($this->composerLock['packages'] as $package) {
            $typeBypass = in_array($package['type'], [
                'drupal-custom-module',
                'drupal-custom-theme',
                'drupal-custom-profile',
            ]);
            if (!$typeBypass && preg_match('[^dev\-|\-dev$]', $package['version'])) {
                $this->composerFailed = true;
                $this->writeln("Package {$package['name']}:{$package['version']} cannot be used in dev version.");
            }
        }

        // Do not allow setting enable-patching.
        if (!empty($composerJson['extra']['enable-patching'])) {
            $this->composerFailed = true;
            $this->writeln("The composer property 'extra.enable-patching' cannot be set to true.");
        }

        // Enforce setting composer-exit-on-patch-failure.
        if (empty($composerJson['extra']['composer-exit-on-patch-failure'])) {
            $this->composerFailed = true;
            $this->writeln("The composer property 'extra.composer-exit-on-patch-failure' must be set to true.");
        }

        // Do not allow remote patches. Check if patches from drupal.org are allowed.
        if (!empty($composerJson['extra']['patches'])) {
            $allowDOrgPatches = !empty($this->getConfig()->get('toolkit.components.composer.drupal_patches'));
            foreach ($composerJson['extra']['patches'] as $packagePatches) {
                foreach ($packagePatches as $patch) {
                    $hostname = parse_url($patch, PHP_URL_HOST);
                    $isDOrg = str_ends_with($hostname ?? '', 'drupal.org');
                    if ($hostname && (!$allowDOrgPatches || !$isDOrg)) {
                        $this->writeln("The patch '$patch' is not valid.");
                        $this->composerFailed = true;
                    }
                }
            }
        }

        // Make sure that the forbidden/obsolete entry is not present in the composer.json file.
        $forbiddenEntries = $this->getConfig()->get('toolkit.components.composer.forbidden');
        // Define common error message.
        $error = 'The forbidden entry "%s" is present in "%s.%s" property of composer.json. Please remove.';
        // Iterate over each forbidden entry and associated details.
        foreach ($forbiddenEntries as $entryName => $forbiddenEntry) {
            // Skip if the entry is not present in the composer.json file.
            if (!isset($composerJson[$entryName])) {
                continue;
            }
            // Detect forbidden entries in composer.json.
            foreach ($forbiddenEntry as $forbiddenKey => $forbiddenValues) {
                if (!isset($composerJson[$entryName][$forbiddenKey])) {
                    continue;
                }
                foreach ((array) $composerJson[$entryName][$forbiddenKey] as $composerKey => $composerValue) {
                    // Determine the check value based on whether it's an associative array or not.
                    $check = (!is_numeric($composerKey) ? $composerKey : $composerValue);
                    // If the check value is found in the forbidden values, display an error message.
                    if (in_array($check, $forbiddenValues)) {
                        $this->io->error(sprintf($error, $check, $entryName, $forbiddenKey));
                        $this->composerFailed = true;
                    }
                }
            }
        }

        // Make sure not installed plugins are not present in composer.json
        $installedPackages = $this->getJson('vendor/composer/installed.json', false);
        if (!empty($composerJson['config']['allow-plugins']) && !empty($installedPackages['packages'])) {
            $composerPlugins = array_filter(
                $installedPackages['packages'],
                fn($package) => isset($package['type']) && $package['type'] === 'composer-plugin'
            );
            $missingPlugins = array_diff(
                array_keys($composerJson['config']['allow-plugins']),
                array_column($composerPlugins, 'name')
            );
            foreach ($missingPlugins as $missingPlugin) {
                $this->io->error("Plugin not installed, please remove from composer.json config.allow-plugins: $missingPlugin.");
                $this->composerFailed = true;
            }
        }

        // Make sure the toolkit-composer-plugin is allowed.
        if (empty($composerJson['config']['allow-plugins'][Toolkit::PLUGIN])) {
            $this->io->error('Plugin ' . Toolkit::PLUGIN . ' must be allowed in the config.allow-plugins section of the composer.json.');
            $this->composerFailed = true;
        }

        if (!$this->composerFailed) {
            $this->say('Composer validation check passed.');
        }
        $this->io->newLine();
    }

    /**
     * Prepare the overrides from config and commit message.
     */
    protected function prepareSkips(): void
    {
        $commitTokens = ToolCommands::getCommitTokens();
        if (isset($commitTokens['skipOutdated']) || !$this->getConfig()->get('toolkit.components.outdated.check')) {
            $this->skipOutdated = true;
        }
        if (!$this->getConfig()->get('toolkit.components.abandoned.check')) {
            $this->skipAbandoned = true;
        }
        if (!$this->getConfig()->get('toolkit.components.unsupported.check')) {
            $this->skipUnsupported = true;
        }
        if (isset($commitTokens['skipInsecure'])) {
            $this->skipInsecure = true;
        }

        // Always mark recommended as skip.
        $this->skipRecommended = true;
    }

    /**
     * Component Configuration Helper - Validate environment variables.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function validateEnvironmentVariables()
    {
        $fileNames = [DockerCommands::DC_YML_FILE, '.env', '.env.dist'];
        $envVarsSet = [];
        // Get forbidden/obsolete vars from config.
        $toolkitReqs = Website::requirements();
        $forbiddenVars = $toolkitReqs['forbidden_variables'] ?? [];
        if (!empty($forbiddenVars)) {
            // Parse files that contain env variables into sets.
            foreach ($fileNames as $filename) {
                if (is_file($filename)) {
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    // Yaml files.
                    if ($ext && $ext == 'yml') {
                        $parsedYaml = Yaml::parseFile($filename);
                        // Loop through all the services looking for environment variables.
                        if (!empty($parsedYaml['services'])) {
                            foreach ($parsedYaml['services'] as $serviceName => $serviceSettings) {
                                if (!empty($serviceSettings['environment'])) {
                                    // Add environment variables set for check.
                                    $envVarsSet[$filename . '_' . $serviceName] = $serviceSettings['environment'];
                                }
                            }
                        }
                        // Ini files.
                    } else {
                        // Add environment variables set for check.
                        $contentParsed = Dotenv::parse(file_get_contents($filename));
                        if (is_array($contentParsed)) {
                            $envVarsSet[$filename] = $contentParsed;
                        }
                    }
                }
            }
            // Detect forbidden variables.
            foreach ($forbiddenVars as $varName) {
                // Check if forbidden env variables are not already here.
                if (getenv($varName) !== false) {
                    $this->configurationFailed = true;
                    $this->io->error('Forbidden environment variable "' . $varName . '" detected in the container. Please locate the source of that variable and remove it.');
                }
                // Find forbidden/obsolete variables in parsed files.
                if (!empty($envVarsSet)) {
                    foreach ($envVarsSet as $filename => $envVars) {
                        if (array_key_exists($varName, $envVars)) {
                            $this->configurationFailed = true;
                            $this->io->error('Forbidden environment variable detected in ' . $filename . ' file: ' . $varName . '. Please remove it.');
                        }
                    }
                }
            }
        }
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
        $skipUnsupported = ($this->skipUnsupported) ? ' (Skipping)' : '';

        $io->definitionList(
            ['Mandatory module check' => $this->getFailedOrPassed($this->mandatoryFailed)],
            ['Recommended module check' => $this->recommendedFailed ? $this->getRecommendedWarningMessage() : 'passed'],
            ['Insecure module check' => $this->getFailedOrPassed($this->insecureFailed) . $skipInsecure],
            ['Outdated module check' => $this->getFailedOrPassed($this->outdatedFailed) . $skipOutdated],
            ['Abandoned module check' => $this->getFailedOrPassed($this->abandonedFailed) . $skipAbandoned],
            ['Unsupported module check' => $this->getFailedOrPassed($this->unsupportedFailed) . $skipUnsupported],
            ['Evaluation module check' => $this->getFailedOrPassed($this->evaluationFailed)],
            ['Development module check' => $this->getFailedOrPassed($this->devCompRequireFailed)],
            ['Composer validation check' => $this->getFailedOrPassed($this->composerFailed)],
            ['Project configuration check' => $this->getFailedOrPassed($this->configurationFailed)],
        );
    }

    /**
     * Helper function to validate the component.
     *
     * @param array $package The package to validate.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function validateComponent(array $package)
    {
        // Ignore if the package is a metapackage.
        if ($package['type'] === 'metapackage') {
            return;
        }
        // Ignore if the package is a dependency hosted inside the project.
        // @see https://getcomposer.org/doc/05-repositories.md#path
        if (!empty($package['transport-options']['relative'])) {
            return;
        }
        $config = $this->getConfig();
        $modules = $this->packageReviews;
        $packageName = $package['name'];
        $isRestricted = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] !== '0';
        $hasBeenQaEd = isset($modules[$packageName]);
        $packageVersion = isset($package['extra']['drupal']['version']) ? explode('+', str_replace('8.x-', '', $package['extra']['drupal']['version']))[0] : $package['version'];

        // Exclude invalid.
        $packageVersion = in_array($packageVersion, $config->get('toolkit.invalid-versions')) ? $package['version'] : $packageVersion;

        // Define vars.
        $message = false;
        $messageType = false;

        // If module was not reviewed yet.
        if (!$hasBeenQaEd) {
            $this->evaluationFailed = true;
            $message = "Package $packageName:$packageVersion has not been reviewed by QA.";
            $messageType = 'Packages not reviewed:';
        }
        if ($hasBeenQaEd) {
            // Validate package version against our constraints.
            $constraints = ['whitelist' => false, 'blacklist' => true];
            foreach ($constraints as $constraint => $result) {
                $constraintValue = !empty($modules[$packageName][$constraint]) ? $modules[$packageName][$constraint] : null;
                if (!is_null($constraintValue) && Semver::satisfies($packageVersion, $constraintValue) === $result) {
                    $this->evaluationFailed = true;
                    $message = "Package $packageName:$packageVersion does not meet the $constraint version constraint: $constraintValue.";
                    $messageType = "Package's version constraints:";
                }
            }

            if (empty($message) && $isRestricted) {
                $projectId = $config->get('toolkit.project_id');
                // Check if the module is allowed for this project id.
                $allowedInProject = in_array($projectId, array_map('trim', explode(',', $modules[$packageName]['restricted_use'])));
                if ($allowedInProject) {
                    $message = "The package $packageName is authorised for the project $projectId";
                    $messageType = 'Packages authorised:';
                }

                // Check if the module is allowed for this type of project.
                $allowedProjectTypes = !empty($modules[$packageName]['allowed_project_types']) ? $modules[$packageName]['allowed_project_types'] : '';
                if (!$allowedInProject && !empty($allowedProjectTypes)) {
                    $allowedProjectTypes = array_map('trim', explode(',', $allowedProjectTypes));
                    // Load the project from the website.
                    $project = Website::projectInformation($projectId);
                    if (in_array($project['type'], $allowedProjectTypes)) {
                        $allowedInProject = true;
                        $message = "The package $packageName is authorised for the type of project {$project['type']}";
                        $messageType = 'Packages authorised:';
                    }
                }

                // Check if the module is allowed for this profile.
                $allowedProfiles = !empty($modules[$packageName]['allowed_profiles']) ? $modules[$packageName]['allowed_profiles'] : '';
                if (!$allowedInProject && !empty($allowedProfiles)) {
                    $allowedProfiles = array_map('trim', explode(',', $allowedProfiles));
                    $profile = $this->getProjectProfile($projectId);
                    if (in_array($profile, $allowedProfiles)) {
                        $allowedInProject = true;
                        $message = "The package $packageName is authorised for the profile $profile";
                        $messageType = 'Packages authorised:';
                    }
                }

                // If module was not allowed in project.
                if (!$allowedInProject) {
                    $this->evaluationFailed = true;
                    $message = "The use of $packageName:$packageVersion is {$modules[$packageName]['status']}.";
                    $messageType = 'Packages rejected/restricted:';
                }
            }
        }
        if ($message && $messageType) {
            return [$message, $messageType];
        }
    }

    /**
     * Loads the composer lock packages.
     */
    private function loadComposerLock(): bool
    {
        if (!empty($this->composerLock['packages'])) {
            return true;
        }
        $this->composerLock = $this->getJson('composer.lock');
        if (!isset($this->composerLock['packages'])) {
            $this->io->error('No packages found in the composer.lock file.');
            return false;
        }
        return true;
    }

    /**
     * Loads the composer outdated results.
     */
    private function loadComposerOutdated(): bool
    {
        if (!empty($this->composerOutdated)) {
            return true;
        }
        $result = $this->taskExec('composer outdated --no-dev --locked --direct --minor-only --no-scripts --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $this->composerOutdated = json_decode($result, true) ?? [];
        return true;
    }

    /**
     * Loads the packages from the website.
     */
    private function loadWebsitePackages(): bool
    {
        if (!empty($this->packageReviews)) {
            return true;
        }
        $data = Website::packages();
        if (empty($data)) {
            $this->io->error('Failed to connect to the endpoint ' . Website::url() . '/api/v1/package-reviews');
            return false;
        }
        $this->packageReviews = array_filter(array_combine(array_column($data, 'name'), $data));
        return true;
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
     * Returns the modules releases.
     *
     * If the update module is not enabled, it will be enabled, and later disabled.
     */
    private function getReleases(): array
    {
        $include = "\Drupal::moduleHandler()->loadInclude('update', 'compare.inc')";
        $command = "update_calculate_project_data(\Drupal::keyValueExpirable('update_available_releases')->getAll())";
        $command = "$include ; echo json_encode($command)";
        $exec = $this->taskExec($this->getBin('drush') . ' eval "' . $command . '"')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        if (empty($exec) || str_contains($exec, 'Call to undefined function')) {
            // Attempt to enable the module only once.
            if ($this->forcedUpdateModule) {
                return [];
            }
            $this->_exec($this->getBin('drush') . ' en update -y');
            $this->forcedUpdateModule = true;
            return $this->getReleases();
        }

        return json_decode($exec, true);
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

    /**
     * Load given project from website and return the profile in the production env.
     *
     * @param string $projectId
     *   The project to use in the endpoint.
     */
    private function getProjectProfile(string $projectId): string
    {
        // Load the project from the website.
        $project = Website::projectInformation($projectId);
        // Get the profile from the production environment.
        if (!empty($project['environments'])) {
            foreach ($project['environments'] as $env) {
                if (!empty($env['profile']) && $env['type'] === 'Production') {
                    return $env['profile'];
                }
            }
        }
        return '';
    }

    /**
     * Ensure that config_readonly is not active by commenting the config line.
     */
    private function disableConfigReadOnly()
    {
        if (ToolCommands::isPackageInstalled('drupal/config_readonly')) {
            $config = $this->getConfig();
            $settings = $config->get('drupal.root') . '/sites/' . $config->get('drupal.site.sites_subdir') . '/settings.php';
            if (file_exists($settings)) {
                $content = file_get_contents($settings);
                if (str_contains($content, 'config_readonly')) {
                    file_put_contents($settings, preg_replace('#(^\$settings\[["|\']config_readonly)#m', "//$1", $content));
                    $this->disabledConfigReadonly = true;
                }
            }
        }
    }

    /**
     * Restore the comment added to the config_readonly setting.
     */
    private function restoreConfigReadOnly()
    {
        if (!$this->disabledConfigReadonly) {
            return;
        }
        $config = $this->getConfig();
        $settings = $config->get('drupal.root') . '/sites/' . $config->get('drupal.site.sites_subdir') . '/settings.php';
        $content = file_get_contents($settings);
        file_put_contents($settings, preg_replace('#^//(\$settings\[["|\']config_readonly)#m', "$1", $content));
    }

}
