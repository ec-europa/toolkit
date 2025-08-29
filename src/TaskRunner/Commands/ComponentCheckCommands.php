<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use Dotenv\Dotenv;
use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Yaml\Yaml;

/**
 * Command class for toolkit:component-check.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class ComponentCheckCommands extends AbstractCommands
{
    protected bool $insecureFailed = false;
    protected bool $outdatedFailed = false;
    protected bool $abandonedFailed = false;
    protected bool $composerFailed = false;
    protected bool $configurationFailed = false;
    protected bool $validatorFailed = false;
    protected bool $savepointsFailed = false;
    protected bool $devCompRequireFailed = false;
    protected bool $skipOutdated = false;
    protected bool $skipAbandoned = false;
    protected bool $skipInsecure = false;
    protected bool $skipInsecureNpm = true;
    protected bool $skipOutdatedNpm = true;
    protected bool $outdatedNpmFailed = false;
    protected bool $insecureNpmFailed = false;
    /**
     * The composer outdated packages.
     *
     * @var array<mixed> $composerOutdated
     */
    protected array $composerOutdated;
    protected $io;
    /**
     * The composer.lock content.
     *
     * @var array<mixed> $composerLock
     */
    protected array $composerLock;
    /**
     * The packages from the website.
     *
     * @var array<mixed> $packageReviews
     */
    protected array $packageReviews;
    /**
     * The opts.yml content.
     *
     * @var array<mixed> $optsYml
     */
    protected array $optsYml;

    /**
     * Check composer for components that are not whitelisted/blacklisted.
     *
     * @command toolkit:component-check
     *
     * @option junit Whether to export results as junit.
     *
     * @aliases tk-components
     *
     * @return int|void
     *   The component check status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function componentCheck(ConsoleIO $io)
    {
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

        $parseOptsFile = $this->getOptsYml();
        // Execute all checks.
        $checks = [
            'componentInsecure' => 'Insecure',
            'componentOutdated' => 'Outdated',
            'componentAbandoned' => 'Abandoned',
            'componentDevelopment' => 'Development',
            'componentComposer' => 'Composer',
            'componentConfiguration' => 'Configuration',
            'componentValidator' => 'Validator',
            'componentSavepoints' => 'Savepoints',
        ];
        // Check if npm_install property enabled adding the NPM checks if so.
        if (!empty($parseOptsFile['npm_install'])) {
            $checks += [
                'componentNpmInsecure' => 'Npm Insecure',
                'componentNpmOutdated' => 'Npm Outdated',
            ];
        }
        foreach ($checks as $function => $label) {
            $io->title("Checking $label components.");
            if ($this->isJunit()) {
                JunitXmlGenerator::addTestCase('Component check', "$label components");
            }
            $this->{$function}($io);
            $io->newLine();
        }

        $this->printComponentResults($io);
        if ($this->isJunit()) {
            JunitXmlGenerator::generate('junit-components.xml');
        }

        // If the validation fail, return according to the blocker.
        $status = 0;
        if (
            $this->devCompRequireFailed ||
            $this->composerFailed ||
            $this->configurationFailed ||
            $this->validatorFailed ||
            $this->savepointsFailed ||
            (!$this->skipInsecureNpm && $this->insecureNpmFailed) ||
            (!$this->skipOutdatedNpm && $this->outdatedNpmFailed) ||
            (!$this->skipOutdated && $this->outdatedFailed) ||
            (!$this->skipAbandoned && $this->abandonedFailed) ||
            (!$this->skipInsecure && $this->insecureFailed)
        ) {
            $io->error('Failed the components check, please verify the report and update the project.');
            $status = 1;
        }

        // Give feedback if no problems found.
        if (!$status) {
            $io->success('Components checked, nothing to report.');
        } else {
            $note = [
                'It is possible to bypass the insecure, outdated and abandoned checks:',
                '- Using commit message to skip Insecure and/or Outdated check:',
                '   - Include in the message: [SKIP-INSECURE] and/or [SKIP-OUTDATED]',
                '',
                '- Using the configuration in the runner.yml.dist as shown below to skip Outdated or Abandoned: ',
                '   toolkit:',
                '     components:',
                '       outdated:',
                '         check: false',
                '       abandoned:',
                '         check: false',
            ];
            if (!empty($parseOptsFile['npm_install'])) {
                $note += [
                    '       npm:',
                    '         outdated:',
                    '           check: false',
                ];
                $note[2] .= ' and/or [SKIP-NPM-INSECURE]';
            }
            $io->note($note);
        }

        return $status;
    }

    /**
     * Check insecure components.
     *
     * @command check:insecure
     *
     * @return int|void
     *   The component insecure result.
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
        $fullSkip = getenv('QA_SKIP_INSECURE') !== false && getenv('QA_SKIP_INSECURE');
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

            $messages[] = $msg;
            $this->insecureFailed = true;
            $this->addJunitResult('Insecure components', $msg, $fullSkip || $this->skipInsecure ? 'warning' : 'error');
        }
        if (!empty($messages)) {
            $this->writeln($messages);
        }

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
     * @return void
     *   The component Outdated result.
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
                    $message = "Package {$package['name']} with version installed {$package['version']} skipped by config.";
                    $io->writeln($message);
                    $this->addJunitResult('Outdated components', $message, 'warning');
                    continue;
                }

                if (!array_key_exists('latest', $package)) {
                    $message = "Package {$package['name']} does not provide information about last version.";
                    $io->writeln($message);
                    $this->addJunitResult('Outdated components', $message, 'warning');
                } elseif (array_key_exists('warning', $package)) {
                    $io->writeln($package['warning']);
                    $this->outdatedFailed = true;
                    $this->addJunitResult('Outdated components', $package['warning']);
                } else {
                    $message = "Package {$package['name']} with version installed {$package['version']} is outdated, please update to last version - {$package['latest']}";
                    $io->writeln($message);
                    $this->outdatedFailed = true;
                    $this->addJunitResult('Outdated components', $message);
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
     *
     * @return mixed
     *   The component abandoned status.
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
                    $this->addJunitResult('Abandoned components', $package['warning']);
                    $this->abandonedFailed = true;
                }
            }
        }
        if (!$this->abandonedFailed) {
            $io->say('Abandoned components check passed.');
        }
    }

    /**
     * Check development components.
     *
     * @command check:development
     *
     * @return int|void
     *   The component development status.
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
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        );
        foreach (array_keys($devPackages) as $packageName) {
            if (ToolCommands::getPackagePropertyFromComposer($packageName, 'version', 'packages')) {
                $this->devCompRequireFailed = true;
                $message = "Package $packageName cannot be used on require section, must be on require-dev section.";
                $this->io->warning($message);
                $this->addJunitResult('Development components', $message);
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
     *
     * @return void
     *   The component configuration status.
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
                    $this->addJunitResult('Configuration components', $validation['message']);
                } else {
                    $this->io->warning($validation['message']);
                    $this->addJunitResult('Configuration components', $validation['message'], 'warning');
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
     * @return void|int
     *   The component composer status.
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
            if (preg_match('[^dev\-|\-dev$]', $package['version'])) {
                $this->composerFailed = true;
                $message = "Package {$package['name']}:{$package['version']} cannot be used in dev version.";
                $this->writeln($message);
                $this->addJunitResult('Composer components', $message);
            }
        }

        // Do not allow setting enable-patching.
        if (!empty($composerJson['extra']['enable-patching'])) {
            $this->composerFailed = true;
            $message = "The composer property 'extra.enable-patching' cannot be set to true.";
            $this->writeln($message);
            $this->addJunitResult('Composer components', $message);
        }

        // Enforce setting composer-exit-on-patch-failure.
        if (empty($composerJson['extra']['composer-exit-on-patch-failure'])) {
            $this->composerFailed = true;
            $message = "The composer property 'extra.composer-exit-on-patch-failure' must be set to true.";
            $this->writeln($message);
            $this->addJunitResult('Composer components', $message);
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
                        $message = sprintf($error, $check, $entryName, $forbiddenKey);
                        $this->io->error($message);
                        $this->composerFailed = true;
                        $this->addJunitResult('Composer components', $message);
                    }
                }
            }
        }

        // Make sure not installed plugins are not present in composer.json.
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
                $message = "Plugin not installed, please remove from composer.json config.allow-plugins: $missingPlugin.";
                $this->io->error($message);
                $this->composerFailed = true;
                $this->addJunitResult('Composer components', $message);
            }
        }

        // Make sure the toolkit-composer-plugin is allowed.
        if (empty($composerJson['config']['allow-plugins'][Toolkit::PLUGIN])) {
            $message = 'Plugin ' . Toolkit::PLUGIN . ' must be allowed in the config.allow-plugins section of the composer.json.';
            $this->io->error($message);
            $this->composerFailed = true;
            $this->addJunitResult('Composer components', $message);
        }

        if (!$this->composerFailed) {
            $this->say('Composer validation check passed.');
        }
        $this->io->newLine();
    }

    /**
     * Run NPM Insecure.
     *
     * @command check:npm-insecure
     *
     * @return int|void
     *   The component NPM insecure status.
     */
    public function componentNpmInsecure()
    {
        $parseOptsFile = $this->getOptsYml();
        // Check if npm_install property enabled.
        if (empty($parseOptsFile['npm_install'])) {
            return ResultData::EXITCODE_OK;
        }

        $this->skipInsecureNpm = false;
        $this->prepareSkips();
        // Generate the package files needed in case not exists.
        if (!file_exists('package-lock.json')) {
            $this->taskExec($this->getBin('run'))->arg('toolkit:setup-eslint')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run()->getMessage();
        }

        $result = $this->taskExec('npm audit --json --audit-level=low --ignore-scripts=true --production --package-lock-only')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $auditModules = json_decode($result, true);
        if (!empty($auditModules['vulnerabilities'])) {
            $this->insecureNpmFailed = true;
            foreach ($auditModules['vulnerabilities'] as $vulnerability) {
                $message = "Package {$vulnerability['name']} has a vulnerability with severity {$vulnerability['severity']}.";
                $this->writeln($message);
                $this->addJunitResult('NPM Insecure', $message, $this->skipInsecureNpm ? 'warning' : 'error');
            }
        }

        if (!$this->insecureNpmFailed) {
            $this->say('NPM Insecure check passed.');
        }
    }

    /**
     * Run NPM Outdated.
     *
     * @command check:npm-outdated
     *
     * @return int|void
     *   The check NPM outdated status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function componentNpmOutdated()
    {
        $parseOptsFile = $this->getOptsYml();
        // Check if npm_install property enabled.
        if (empty($parseOptsFile['npm_install'])) {
            return ResultData::EXITCODE_OK;
        }

        $this->skipOutdatedNpm = false;
        $this->prepareSkips();
        // Generate the package files needed in case not exists.
        if (!file_exists('package-lock.json')) {
            $this->taskExec($this->getBin('run'))->arg('toolkit:setup-eslint')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run()->getMessage();
        }

        $result = $this->taskExec('npm outdated --json --long')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $outdatedModules = json_decode($result, true);
        if (empty($outdatedModules)) {
            $this->say('NPM Outdated check passed.');
        } else {
            foreach ($outdatedModules as $packageName => $package) {
                if ($package['current'] !== $package['latest']) {
                    $message = "Package {$packageName} with version installed {$package['current']} is outdated, please update to the {$package['latest']} version.";
                    $this->writeln($message);
                    $this->outdatedNpmFailed = true;
                    $this->addJunitResult('NPM Outdated', $message, $this->skipOutdatedNpm ? 'warning' : 'error');
                }
            }
            if (!$this->outdatedNpmFailed) {
                // Check is passed if modules reported have same current-latest version.
                $this->say('NPM Outdated check passed.');
            }
        }
    }

    /**
     * Check project configuration.
     *
     * @command check:validator
     *
     * @return void
     *   The component validator status.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function componentValidator(ConsoleIO $io)
    {
        $this->io = $io;

        $runnerBin = $this->getBin('run');
        $result = $this->taskExecStack()
            ->exec("$runnerBin moodle:plugin-validator")
            ->run();

        if ($result->getExitCode() === ResultData::EXITCODE_ERROR) {
            $this->io->error($result->getMessage());
            $this->validatorFailed = true;
            $this->addJunitResult('Plugin validator', $result->getMessage());
        }

        if (!$this->validatorFailed) {
            $this->say('Plugin validator check passed.');
        }
        $this->io->newLine();
    }

    /**
     * Check component savepoints.
     *
     * @command check:savepoints
     *
     * @return void
     *   The component savepoints status.
     */
    public function componentSavepoints(ConsoleIO $io)
    {
        $this->io = $io;

        $runnerBin = $this->getBin('run');
        $result = $this->taskExecStack()
            ->exec("$runnerBin moodle:plugin-savepoints")
            ->run();

        if ($result->getExitCode() === ResultData::EXITCODE_ERROR) {
            $this->io->error($result->getMessage());
            $this->savepointsFailed = true;
            $this->addJunitResult('Plugin savepoints', $result->getMessage());
        }

        if (!$this->savepointsFailed) {
            $this->say('Plugin savepoints check passed.');
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
        if (isset($commitTokens['skipInsecureNpm'])) {
            $this->skipInsecureNpm = true;
        }
        if (!$this->getConfig()->get('toolkit.components.npm.outdated.check')) {
            $this->skipOutdatedNpm = true;
        }
        if (!$this->getConfig()->get('toolkit.components.abandoned.check')) {
            $this->skipAbandoned = true;
        }
        if (isset($commitTokens['skipInsecure'])) {
            $this->skipInsecure = true;
        }
    }

    /**
     * Component Configuration Helper - Validate environment variables.
     *
     * @return void
     *   Environment variable validated.
     *
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function validateEnvironmentVariables()
    {
        $fileNames = [DockerCommands::DC_YML_FILE, '.env', '.env.dist'];
        $envVarsSet = [];
        // Get forbidden/obsolete vars from config.
        $toolkitRequirements = Website::requirements();
        $forbiddenVars = $toolkitRequirements['forbidden_variables'] ?? [];
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
     *
     * @return void
     *   The printed component list results.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function printComponentResults(ConsoleIO $io)
    {
        $io->title('Results:');
        $parseOptsFile = $this->getOptsYml();
        $headers = [
            'Insecure plugin check',
            'Outdated plugin check',
            'Abandoned plugin check',
            'Development plugin check',
            'Composer validation check',
            'Project configuration check',
            'Plugin validator check',
            'Plugin savepoints check',
        ];
        $rows = [
            $this->getFailedOrPassed($this->insecureFailed) . ($this->skipInsecure ? ' (Skipping)' : ''),
            $this->getFailedOrPassed($this->outdatedFailed) . ($this->skipOutdated ? ' (Skipping)' : ''),
            $this->getFailedOrPassed($this->abandonedFailed) . ($this->skipAbandoned ? ' (Skipping)' : ''),
            $this->getFailedOrPassed($this->devCompRequireFailed),
            $this->getFailedOrPassed($this->composerFailed),
            $this->getFailedOrPassed($this->configurationFailed),
            $this->getFailedOrPassed($this->validatorFailed),
            $this->getFailedOrPassed($this->savepointsFailed),
        ];

        // Check if npm_install property is enabled and add the NPM results.
        if (!empty($parseOptsFile['npm_install'])) {
            $headers[] = 'NPM Insecure check';
            $headers[] = 'NPM Outdated check';
            $rows[] = $this->getFailedOrPassed($this->insecureNpmFailed) . ($this->skipInsecureNpm ? ' (Skipping)' : '');
            $rows[] = $this->getFailedOrPassed($this->outdatedNpmFailed) . ($this->skipOutdatedNpm ? ' (Skipping)' : '');
        }
        $io->horizontalTable($headers, [$rows]);
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
     * Returns the .opts.yml content.
     *
     * @return array<mixed>
     *   The opts.yml file content.
     */
    private function getOptsYml(): array
    {
        if (isset($this->optsYml)) {
            return $this->optsYml;
        }
        return ToolCommands::parseOptsYml() ?: [];
    }

    /**
     * Add a result to junit if it is enabled.
     *
     * All test case will be added to the test suite 'Component check'.
     *
     * @param string $testCase
     *   The name of the test.
     * @param string $message
     *   The message for the failure.
     * @param string $type
     *   The type of failure.
     *
     * @see JunitXmlGenerator::addResult()
     */
    private function addJunitResult(string $testCase, string $message, string $type = 'error'): void
    {
        // Skip if no junit option is used.
        if (!$this->isJunit()) {
            return;
        }
        JunitXmlGenerator::addResult('Component check', $testCase, $message, $type);
    }

}
