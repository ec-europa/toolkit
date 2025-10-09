<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command class for moodle:component-check.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MoodleComponentCheckCommands extends ComponentCheckCommands
{
    protected bool $validatorFailed = false;
    protected bool $savepointsFailed = false;

    /**
     * Check composer for components that are not whitelisted/blacklisted.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:component-check
     *
     * @option endpoint     The endpoint to use to connect to QA Website.
     * @option test-command If set the command will load test packages.
     * @option junit        Whether to export results as junit.
     *
     * @aliases md-components
     *
     * @return int|void
     *   The component check status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
     */
    public function componentCheck(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
        'test-command' => false,
    ])
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
        if (
            empty($composerJson['replace'][Toolkit::PLUGIN]) &&
            empty($composerJson['config']['allow-plugins'][Toolkit::PLUGIN])
        ) {
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

}
