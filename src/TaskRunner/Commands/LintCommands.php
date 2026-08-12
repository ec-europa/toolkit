<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\TaskException;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;

/**
 * Commands to lint the source code and interact with ESLint.
 */
class LintCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/lint.yml';
    }

    /**
     * Setup the ESLint configurations and dependencies.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:setup-eslint
     *
     * @option config      The eslint config file.
     * @option ignores     The patterns to ignore.
     * @option drupal-root The drupal root.
     * @option packages    The npm packages to install.
     * @option force       If true, the config file will be deleted.
     *
     * @return int
     *   The setup eslint configuration status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitSetupEslint(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'ignores' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'drupal-root' => InputOption::VALUE_REQUIRED,
        'packages' => InputOption::VALUE_REQUIRED,
        'force' => false,
    ])
    {
        Toolkit::ensureArray($options['ignores']);

        $actions = false;
        $config = $options['config'];
        if ($options['force'] && file_exists($config)) {
            $actions = true;
            $this->taskExec('rm')->arg($config)->run();
        }

        // Create a package.json if it doesn't exist.
        $packageJson = 'package.json';
        if (!file_exists($packageJson) || !file_exists($this->getNodeBinPath('eslint'))) {
            $actions = true;
            $this->_exec('pnpm init && pnpm pkg set name="@scope/`basename $PWD`"');
            $overrides = $this->getConfigValue('toolkit.lint.eslint.overrides', []);
            if (file_exists($packageJson) && !empty($overrides)) {
                $packageData = json_decode(file_get_contents($packageJson), true);
                $packageData['overrides'] = $overrides;
                file_put_contents($packageJson, json_encode($packageData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            $this->_exec("pnpm add --save-dev {$options['packages']}");
        }

        // Check if the binary exists.
        try {
            $this->getNodeBin('eslint');
        } catch (TaskException $e) {
            $actions = true;
            $this->taskExec('pnpm install')->run();
        }

        if (!file_exists($config)) {
            $actions = true;
            $this->generateEslintConfigurations($config, $options);
        }

        // Ignore all yaml files for prettier.
        if (!file_exists('.prettierignore')) {
            $actions = true;
            $this->taskWriteToFile('.prettierignore')->text('*.yml')->run();
        }

        if (!$actions) {
            $this->say('No actions needed.');
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Generate configurations for ESLint.
     *
     * @param string $config
     *   The path for the configuration file.
     * @param array<mixed> $options
     *   The options passed to the command.
     *
     * @return void
     *   The eslint configuration generated.
     */
    private function generateEslintConfigurations(string $config, array $options)
    {
        $data = [
            'ignorePatterns' => $options['ignores'],
            // The docker-compose file makes use of
            // empty mappings in env variables.
            'overrides' => [
                [
                    'files' => ['docker-compose*.yml'],
                    'rules' => ['yml/no-empty-mapping-value' => 'off'],
                ],
            ],
        ];

        // Check if we have a Drupal environment.
        $drupalCore = './' . $options['drupal-root'] . '/core';
        if (file_exists($drupalCore)) {
            // Add the drupal core eslint if it exists.
            $drupalEslint = './' . $options['drupal-root'] . '/core/.eslintrc.json';
            if (file_exists($drupalEslint)) {
                $data['extends'] = $drupalEslint;
            }

            // Copy the prettier configurations from Drupal or fallback to defaults.
            $prettier = './' . $options['drupal-root'] . '/core/.prettierrc.json';
            $prettier = file_exists($prettier)
                ? json_decode(file_get_contents($prettier), true)
                : ['singleQuote' => true, 'printWidth' => 80, 'semi' => true, 'trailingComma' => 'all'];
            $data['rules'] = [
                'prettier/prettier' => ['error', $prettier],
            ];
        }

        $this->collectionBuilder()->addCode(function () use ($config, $data) {
            $this->output()->writeln(" <fg=white;bg=cyan;options=bold>[File\Write]</> Writing to $config.<info></>");
            file_put_contents($config, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        })->run();
    }

    /**
     * Run lint YAML.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:lint-yaml
     *
     * @option config     The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     * @option junit      Whether to export results as junit.
     *
     * @aliases tk-yaml, tly
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The Toolkit lint yaml command status.
     *
     * @usage --extensions='.yml' --options='fix no-eslintrc'
     */
    public function toolkitLintYaml(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        Toolkit::ensureArray($options['extensions']);

        return $this->toolkitRunEsLint($options['config'], $options['extensions'], $options['options']);
    }

    /**
     * Run lint JS.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:lint-js
     *
     * @option config     The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     * @option junit      Whether to export results as junit.
     *
     * @aliases tk-js, tljs
     *
     * @usage --extensions='.js' --options='fix no-eslintrc'
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The toolkit lint js status.
     */
    public function toolkitLintJs(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        Toolkit::ensureArray($options['extensions']);

        return $this->toolkitRunEsLint($options['config'], $options['extensions'], $options['options']);
    }

    /**
     * Execute the eslint.
     *
     * @param string $config
     *   The eslint config file.
     * @param array<mixed> $extensions
     *   The extensions to check.
     * @param string $options
     *   Extra options for the command.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The toolkit run eslint status.
     *
     * @see toolkitLintYaml()
     * @see toolkitLintJs()
     */
    private function toolkitRunEsLint(string $config, array $extensions, string $options)
    {
        $tasks = [];

        $tasks[] = $this->taskExec($this->getBin('run'))->arg('toolkit:setup-eslint');

        $opts = [
            'config' => $config,
            'ext' => implode(',', $extensions),
        ];

        if (!empty($options)) {
            $extra = array_fill_keys(explode(' ', $options), null);
            $opts = array_merge($opts, $extra);
        }

        if ($this->isJunit()) {
            $opts['format'] = 'junit';
            $type = str_ends_with($this->input()->getArgument('command'), 'js') ? 'js' : 'yaml';
            $opts['output-file'] = JunitXmlGenerator::$dir . "/junit-lint-$type.xml";
        }

        $tasks[] = $this->taskExec($this->getNodeBinPath('eslint'))->options($opts)->arg('.');

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run lint PHP.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:lint-php
     *
     * @option exclude    The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     * @option junit      Whether to export results as junit.
     *
     * @aliases tk-php, tlp
     *
     * @return int
     *   The toolkit lint php command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toolkitLintPhp(array $options = [
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'exclude' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        Toolkit::ensureArray($options['extensions']);
        Toolkit::ensureArray($options['exclude']);

        $task = $this->taskExec($this->getBin('parallel-lint'));
        foreach ($options['exclude'] as $exclude) {
            $task->option('exclude', $exclude);
        }
        if ($options['extensions']) {
            $task->option('-e', implode(',', $options['extensions']));
        }
        if (!empty($options['options'])) {
            $opts = explode(' ', $options['options']);
            foreach ($opts as $opt) {
                $task->option($opt);
            }
        }

        $task->rawArg('.');

        if ($this->isJunit()) {
            JunitXmlGenerator::addTestSuite('Lint PHP');
            JunitXmlGenerator::addTestCase('Lint PHP', 'Lint PHP');
            $result = $task->option('json')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run();
            $findings = json_decode($result->getMessage(), true);
            if (!empty($findings['results']['errors'])) {
                foreach ($findings['results']['errors'] as $find) {
                    $this->writeln($find['message']);
                    JunitXmlGenerator::addResult('Lint PHP', 'Lint PHP', $find['message']);
                }
            }
            JunitXmlGenerator::generate('junit-lint-php.xml');
        } else {
            $result = $task->run();
        }

        // Exit code 254 is when no files are found to lint.
        if (empty($result->getExitCode()) || $result->getExitCode() === 254) {
            return ResultData::EXITCODE_OK;
        }
        return $result->getExitCode();
    }

    /**
     * Run lint CSS.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:lint-css
     *
     * @option exclude The stylelint config file.
     * @option files   The files to check.
     * @option options Extra options for the command without -- (only options with no value).
     * @option junit   Whether to export results as junit.
     *
     * @aliases tk-css
     *
     * @return int
     *   The toolkit lint css command status.
     */
    public function toolkitLintCss(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'files' => InputOption::VALUE_REQUIRED,
        'options' => InputOption::VALUE_REQUIRED,
    ])
    {
        // Make sure eslint is properly installed.
        $this->taskExec($this->getBin('run'))->arg('toolkit:setup-eslint')->run();

        // Make sure the stylelint-config-drupal and stylelint are installed.
        $this->_exec('[ -f package.json ] || (pnpm init && pnpm pkg set name="@scope/`basename $PWD`")');
        $this->_exec('pnpm list stylelint-config-drupal >/dev/null 2>&1 && pnpm update stylelint-config-drupal || pnpm add stylelint-config-drupal');

        // Generate the config file if missing.
        if (!file_exists($options['config'])) {
            $data = ['extends' => 'stylelint-config-drupal'];
            $this->taskWriteToFile($options['config'])
                ->text(json_encode($data, JSON_PRETTY_PRINT))
                ->run();
        }

        $task = $this->taskExec($this->getNodeBinPath('stylelint'))
            ->rawArg($options['files']);

        if (!empty($options['options'])) {
            $opts = explode(' ', $options['options']);
            foreach ($opts as $opt) {
                $task->option($opt);
            }
        }

        if ($this->isJunit()) {
            JunitXmlGenerator::addTestCase('Lint CSS', 'Lint CSS');
            $result = $task->option('formatter', 'json', '=')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run();
            $findings = json_decode($result->getMessage(), true);
            $workingDir = $this->getWorkingDir();
            if (!empty($findings)) {
                foreach ($findings as $find) {
                    if (empty($find['errored'])) {
                        continue;
                    }
                    $this->writeln(str_replace($workingDir, '', $find['source']));
                    foreach ($find['warnings'] as $warning) {
                        $text = sprintf(' %d:%d %s %s', $warning['line'], $warning['column'], $warning['text'], $warning['rule']);
                        $this->writeln($text);
                        JunitXmlGenerator::addResult('Lint CSS', 'Lint CSS', $text);
                    }
                }
            }
            JunitXmlGenerator::generate('junit-lint-css.xml');
            return $result->getExitCode();
        }

        $result = $task->run();
        return $result->getExitCode();
    }

    /**
     * Run lint CSpell.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:lint-cspell
     *
     * @option config  The path to the config file.
     * @option files   The files to check.
     * @option options Extra options for the command.
     * @option junit   Whether to export results as junit.
     *
     * @aliases tk-cspell
     *
     * @usage --files='lib' --config='web/core/.cspell.json' --options='--gitignore'
     *
     * @return int
     *   The toolkit lint-cspell command status.
     */
    public function toolkitLintCsPell(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'files' => InputOption::VALUE_REQUIRED,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $bin = $this->getNodeBinPath('cspell');

        // Install dependencies if the bin is not present.
        if (!file_exists($bin)) {
            $this->_exec('[ -f package.json ] || (pnpm init && pnpm pkg set name="@scope/`basename $PWD`")');
            $this->_exec('pnpm list cspell >/dev/null 2>&1 && pnpm update cspell || pnpm add cspell');
        }

        // Ensure the config file exists.
        if (!file_exists($options['config'])) {
            $this->taskProcess(
                Toolkit::getToolkitRoot() . '/resources/cspell/.project-cspell.json',
                $options['config']
            )->run();
        }

        $command = $bin . ' ' . $options['files'] . ' --config=' . $options['config'];
        $task = $this->taskExec($command . ' ' . $options['options']);

        if ($this->isJunit()) {
            JunitXmlGenerator::addTestCase('CSpell', 'CSpell');
            $result = $task->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)->run();
            $message = $result->getMessage();
            if (!empty($message)) {
                $this->writeln($message);
                $findings = array_filter(explode(PHP_EOL, $message));
                foreach ($findings as $find) {
                    JunitXmlGenerator::addResult('CSpell', 'CSpell', $find);
                }
            }
            JunitXmlGenerator::generate('junit-cspell.xml');
            return $result->getExitCode();
        }

        $result = $task->run();
        return $result->getExitCode();
    }

    /**
     * Run lint Behat.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:lint-behat
     *
     * @option config  The path to the config file.
     * @option files   The files to check.
     * @option junit   Whether to export results as junit.
     *
     * @aliases tk-lbehat
     *
     * @usage --files='tests/features' --config='gherkinlint.json'
     *
     * @return int
     *   The toolkit lint-behat command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toolkitLintBehat(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'files' => InputOption::VALUE_REQUIRED,
    ])
    {
        // Ensure the config file exists.
        if (!file_exists($options['config'])) {
            $this->output->writeln('Could not find the config file, the default will be created in the project root.');
            $this->taskFilesystemStack()->copy(
                Toolkit::getToolkitRoot() . '/resources/gherkinlint.json',
                $options['config']
            )->run();
        }

        $task = $this->taskExec($this->getBinPath('gherkinlint') . ' lint ' . $options['files']);

        if ($this->isJunit()) {
            JunitXmlGenerator::addTestSuite('Lint Behat');
            $result = $task->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)->run();
            $table = $result->getMessage();
            $findings = array_filter(explode(PHP_EOL, $table), function ($item) {
                $featureLine = str_ends_with($item, '.feature');
                $validRow = str_starts_with($item, '|') && !str_starts_with($item, '| line');
                return !empty($item) && ($validRow || $featureLine);
            });
            if (!empty($findings)) {
                $this->writeln($table);
                foreach ($findings as $find) {
                    if (str_ends_with($find, '.feature')) {
                        $featureName = $find;
                        JunitXmlGenerator::addTestCase('Lint Behat', $featureName);
                        continue;
                    }
                    $exploded = explode('|', trim($find, '|'));
                    [$line, $column, $severity, $message] = array_map('trim', $exploded);
                    if (empty($line) || empty($column) || empty($severity) || empty($message)) {
                        continue;
                    }
                    if (isset($featureName)) {
                        JunitXmlGenerator::addResult('Lint Behat', $featureName, "$line:$column - $severity - $message");
                    }
                }
            }
            JunitXmlGenerator::generate('junit-lint-behat.xml');
            return $result->getExitCode();
        }

        $result = $task->run();
        return $result->getExitCode();
    }

}
