<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\AbortTasksException;
use Robo\Exception\TaskException;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestsCommands.
 */
class TestsCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/test.yml';
    }

    /**
     * Setup PHP code sniffer.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpcs'.
     *
     * @command toolkit:setup-phpcs
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitSetupPhpcs()
    {
        $config = $this->getConfig();
        $config_file = $config->get('toolkit.test.phpcs.config');
        if (file_exists($config_file)) {
            $this->taskExec('rm')->arg($config_file)->run();
        }

        $phpcs_xml = new \DOMDocument('1.0', 'UTF-8');
        $phpcs_xml->formatOutput = true;
        // Root element.
        $root = $phpcs_xml->createElement('ruleset');
        $root->setAttribute('name', 'QA');
        $phpcs_xml->appendChild($root);
        $root->appendChild($phpcs_xml->createElement('description', 'QA PHPcs Ruleset'));

        // Handle standards.
        $root->appendChild($phpcs_xml->createComment(' Standards. '));
        if (!empty($standards = $config->get('toolkit.test.phpcs.standards'))) {
            foreach ($standards as $standard) {
                $element = $phpcs_xml->createElement('rule');
                $element->setAttribute('ref', $standard);
                $root->appendChild($element);
            }
        }
        $root->appendChild($phpcs_xml->createComment(' Arguments. '));
        // Handle file extensions.
        if (!empty($extensions = $config->get('toolkit.test.phpcs.triggered_by'))) {
            $element = $phpcs_xml->createElement('arg');
            $element->setAttribute('name', 'extensions');
            $element->setAttribute('value', implode(',', array_values($extensions)));
            $root->appendChild($element);
        }
        // Handle argument report.
        $element = $phpcs_xml->createElement('arg');
        $element->setAttribute('name', 'report');
        $element->setAttribute('value', 'full');
        $root->appendChild($element);
        // Handle argument color.
        $element = $phpcs_xml->createElement('arg');
        $element->setAttribute('name', 'colors');
        $root->appendChild($element);
        // Handle argument progress.
        $element = $phpcs_xml->createElement('arg');
        $element->setAttribute('value', 'p');
        $root->appendChild($element);
        // Handle show sniffs.
        if ($config->get('toolkit.test.phpcs.show_sniffs') === 'true') {
            $element = $phpcs_xml->createElement('arg');
            $element->setAttribute('value', 's');
            $root->appendChild($element);
        }
        // Handle the files.
        $root->appendChild($phpcs_xml->createComment(' Files to check. '));
        if (!empty($files = $config->get('toolkit.test.phpcs.files'))) {
            $files = is_string($files) ? explode(',', $files) : $files;
            Toolkit::filterFolders($files);
            foreach ($files as $file) {
                $root->appendChild($phpcs_xml->createElement('file', $file));
            }
        } else {
            $root->appendChild($phpcs_xml->createElement('file', '.'));
        }
        // Handle exclude patterns.
        $root->appendChild($phpcs_xml->createComment(' Exclude patterns. '));
        if (!empty($ignores = $config->get('toolkit.test.phpcs.ignore_patterns'))) {
            foreach ($ignores as $ignore) {
                $root->appendChild($phpcs_xml->createElement('exclude-pattern', $ignore));
            }
        }

        $root->appendChild($phpcs_xml->createComment(' Add your custom rules after this line. '));
        $this->taskWriteToFile($config_file)
            ->text($phpcs_xml->saveXML())->run();
    }

    /**
     * Run PHP code sniffer.
     *
     * @command toolkit:test-phpcs
     *
     * @aliases tk-phpcs
     *
     * @see toolkitRunPhpcs()
     */
    public function toolkitTestPhpcs()
    {
        $mode = $this->getConfig()->get('toolkit.test.phpcs.mode', 'phpcs');
        if ($mode === 'grumphp') {
            $this->say('Executing PHPcs within GrumPHP.');
            return $this->toolkitRunGrumphp();
        } else {
            $this->say('Executing PHPcs.');
            return $this->toolkitRunPhpcs();
        }
    }

    /**
     * Run PHPMD.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpmd'.
     *
     * @command toolkit:test-phpmd
     *
     * @option config           The config file.
     * @option format           The format to use.
     * @option ignore_patterns  An array with ignore patterns.
     * @option triggered_by     An array with extensions to check.
     * @option files            An array with paths to check.
     *
     * @aliases tk-phpmd
     */
    public function toolkitTestPhpmd(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'format' => InputOption::VALUE_REQUIRED,
        'ignore_patterns' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'triggered_by' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'files' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
    ])
    {
        $phpmd_bin = $this->getBin('phpmd');
        $phpmd_config = $this->getConfig()->get('toolkit.test.phpmd');
        $config_file = $phpmd_config['config'];
        $format = $phpmd_config['format'];
        $options = '';

        if (!file_exists($config_file)) {
            $this->output->writeln('Could not find the ruleset file, the default will be created in the project root.');
            $this->_copy(Toolkit::getToolkitRoot() . '/resources/phpmd.xml', $config_file);
        }

        if (!empty($phpmd_config['ignore_patterns'])) {
            $options .= '--exclude "' . implode(',', $phpmd_config['ignore_patterns']) . '" ';
        }
        if (!empty($phpmd_config['triggered_by'])) {
            $options .= '--suffixes "' . implode(',', $phpmd_config['triggered_by']) . '" ';
        }

        Toolkit::filterFolders($phpmd_config['files']);
        $files = implode(',', $phpmd_config['files']);

        return $this->taskExec("$phpmd_bin $files $format $config_file $options")
            ->run();
    }

    /**
     * Run PHP code sniffer within GrumPHP.
     *
     * @throws TaskException
     *
     * @deprecated
     */
    protected function toolkitRunGrumphp()
    {
        $bin = $this->getBin('grumphp');
        $grumphpFile = './grumphp.yml.dist';
        $containsQaConventions = false;

        if (file_exists($grumphpFile)) {
            $grumphpArray = (array) Yaml::parse(file_get_contents($grumphpFile));
            if (isset($grumphpArray['imports'])) {
                foreach ($grumphpArray['imports'] as $import) {
                    if (isset($import['resource']) && $import['resource'] === 'vendor/ec-europa/qa-automation/dist/qa-conventions.yml') {
                        $containsQaConventions = true;
                    }
                }
            }
        }

        $composerFile = './composer.json';
        if (file_exists($composerFile)) {
            $composerArray = json_decode(file_get_contents($composerFile), true);
            if (isset($composerArray['extra']['grumphp']['config-default-path'])) {
                $configDefaultPath = $composerArray['extra']['grumphp']['config-default-path'];
                $this->say('You should remove the following from your composer.json extra array:');
                echo "\n\"grumphp\": {\n    \"config-default-path\": \"$configDefaultPath\"\n}\n\n";
            }
        }

        if ($containsQaConventions) {
            return $this->taskExec("$bin run")->run();
        } else {
            $this->say('All Drupal projects in the ec-europa namespace need to use Quality Assurance provided standards.');
            $this->say('Your configuration has to import the resource vendor/ec-europa/qa-automation/dist/qa-conventions.yml.');
            $this->say('For more information visit: https://github.com/ec-europa/toolkit/blob/release/4.x/docs/testing-project.md#phpcs-testing');
            $this->say('Add the following lines to your grumphp.yml.dist:');
            echo "\nimports:\n  - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }\n\n";
            return new ResultData(1);
        }
    }

    /**
     * Run PHP code sniffer.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpcs'.
     */
    protected function toolkitRunPhpcs()
    {
        $config = $this->getConfig();
        $phpcs_bin = $this->getBin('phpcs');
        $config_file = $config->get('toolkit.test.phpcs.config');

        $this->toolkitCheckPhpcsRequirements();

        $options = '';
        if ($config->get('toolkit.test.phpcs.ignore_annotations') === 'true') {
            $options .= ' --ignore-annotations';
        }
        return $this->taskExec("$phpcs_bin --standard=$config_file$options")
            ->run();
    }

    /**
     * Make sure that the config file exists and configuration is correct.
     *
     * @command toolkit:check-phpcs-requirements
     */
    public function toolkitCheckPhpcsRequirements()
    {
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');
        if (!file_exists($config_file)) {
            $this->say('Calling toolkit:setup-phpcs.');
            $this->toolkitSetupPhpcs();
        }

        // Skip standards check for Toolkit.
        if (getcwd() === Toolkit::getToolkitRoot()) {
            return;
        }

        // Make sure the required standards are present.
        $standards = [
            './vendor/drupal/coder/coder_sniffer/Drupal',
            './vendor/drupal/coder/coder_sniffer/DrupalPractice',
            './vendor/ec-europa/qa-automation/phpcs/QualityAssurance',
        ];
        $rules = [];
        $data = simplexml_load_file($config_file);
        foreach ($data->rule as $item) {
            if (isset($item['ref'])) {
                $rules[] = (string) $item['ref'];
            }
        }
        if ($diff = array_diff($standards, $rules)) {
            throw new AbortTasksException("The following standards are missing, please add them to the configuration file '$config_file'.\n" . implode("\n", $diff));
        }
    }

    /**
     * Run PHPStan.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpstan'.
     *
     * @command toolkit:test-phpstan
     *
     * @option config The path to the config file.
     * @option level  The level of rule options.
     * @option files  The files to check.
     *
     * @aliases tk-phpstan
     */
    public function toolkitTestPhpstan(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'level' => InputOption::VALUE_REQUIRED,
        'files' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
    ])
    {
        $config = $this->getConfig();
        // Only run if we find a Drupal installation.
        if (!file_exists($config->get('drupal.root'))) {
            $this->say('Could not find a Drupal installation, skipping.');
            return 0;
        }
        $tasks = [];
        Toolkit::filterFolders($options['files']);
        $ignores = $config->get('toolkit.test.phpstan.ignores');
        Toolkit::filterFolders($ignores);

        // If the config file is not found, generate a new one.
        if (!file_exists($options['config'])) {
            $config_content = [
                'parameters' => [
                    'drupal' => [
                        'drupal_root' => getcwd() . '/' . $config->get('drupal.root'),
                    ],
                    'level' => $options['level'],
                    'paths' => array_values($options['files']),
                    'excludePaths' => $ignores,
                ],
            ];
            $tasks[] = $this->taskWriteToFile($options['config'])
                ->text(Yaml::dump($config_content, 10, 2));
        }
        $tasks[] = $this->taskExec($this->getBin('phpstan'))
            ->arg('analyse')
            ->option('configuration', $options['config']);
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run Behat tests.
     *
     * Check configurations at config/default.yml - 'toolkit.test.behat'.
     * Accept commands to run before and/or after the Behat tests.
     *
     * @command toolkit:test-behat
     *
     * @aliases tb, tk-behat
     *
     * @option from     The dist config file (behat.yml.dist).
     * @option to       The destination config file (behat.yml).
     * @option profile  The profile to execute.
     * @option suite    The suite to execute, default runs all suites of profile.
     * @option options  Extra options for the command without -- (only options with no value).
     *
     * @usage --profile='prod' --options='strict stop-on-failure'
     */
    public function toolkitTestBehat(array $options = [
        'from' => InputOption::VALUE_OPTIONAL,
        'to' => InputOption::VALUE_OPTIONAL,
        'profile' => InputOption::VALUE_OPTIONAL,
        'suite' => InputOption::VALUE_OPTIONAL,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $tasks = [];

        if (Toolkit::isCiCd()) {
            $this->taskExec($this->getBin('run') . ' toolkit:install-dependencies')->run();
        }

        $behatBin = $this->getBin('behat');
        $defaultProfile = $this->getConfig()->get('toolkit.test.behat.profile');
        $execOpts = [
            'profile' => !empty($options['profile']) ? $options['profile'] : $defaultProfile,
        ];

        if (!empty($options['suite'])) {
            $execOpts['suite'] = $options['suite'];
        }
        if (!empty($options['options'])) {
            $extraOptions = array_fill_keys(explode(' ', $options['options']), null);
            $execOpts = array_merge($execOpts, $extraOptions);
        }

        // Execute a list of commands to run before tests.
        if ($commands = $this->getConfig()->get('toolkit.test.behat.commands.before')) {
            $tasks[] = $this->taskExecute($commands);
        }

        $this->taskProcess($options['from'], $options['to'])->run();

        $result = $this->taskExec($behatBin)->options($execOpts + ['dry-run' => null], '=')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        if (str_contains(trim($result), 'No scenarios')) {
            $this->say("No Scenarios found for profile {$execOpts['profile']}, please create at least one Scenario.");
            return new ResultData(1);
        }

        $tasks[] = $this->taskExec($behatBin)->options($execOpts, '=');

        // Execute a list of commands to run after tests.
        if ($commands = $this->getConfig()->get('toolkit.test.behat.commands.after')) {
            $tasks[] = $this->taskExecute($commands);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run PHPUnit tests.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpunit'.
     * Accept commands to run before and/or after the PHPUnit tests.
     *
     * @command toolkit:test-phpunit
     *
     * @aliases tp, tk-phpunit
     *
     * @option execution   The execution type (default or parallel).
     * @option from        The dist config file (phpunit.xml.dist).
     * @option to          The destination config file (phpunit.xml).
     */
    public function toolkitTestPhpunit(array $options = [
        'execution' => InputOption::VALUE_REQUIRED,
        'from' => InputOption::VALUE_REQUIRED,
        'to' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];

        if (file_exists($options['from'])) {
            $this->taskProcess($options['from'], $options['to'])->run();
        }

        if (!file_exists($options['to'])) {
            $this->say('PHUnit configuration not found, skipping.');
            return $this->collectionBuilder()->addTaskList($tasks);
        }

        $phpunit_config = $this->getConfig()->get('toolkit.test.phpunit');

        // Execute a list of commands to run before tests.
        if (!empty($phpunit_config['commands']['before'])) {
            $tasks[] = $this->taskExecute($phpunit_config['commands']['before']);
        }

        $options = $phpunit_config['options'];
        $phpunit_bin = $this->getBin('phpunit');

        if ($phpunit_config['execution'] == 'parallel') {
            $result = $this->taskExec("$phpunit_bin --list-suites")
                ->silent(true)
                ->printOutput(false)
                ->run()
                ->getMessage();

            $suites = preg_grep('/^( - [\w\-]+)/', explode("\n", $result));

            $tasks[] = $parallel = $this->taskParallelExec();
            foreach ($suites as $suite) {
                $suite = str_replace('- ', '', trim($suite));
                if (strlen($suite) > 2) {
                    $parallel->process("$phpunit_bin --testsuite=$suite $options");
                }
            }
        } else {
            $tasks[] = $this->taskExec("$phpunit_bin $options");
        }

        // Execute a list of commands to run after tests.
        if (!empty($phpunit_config['commands']['after'])) {
            $tasks[] = $this->taskExecute($phpunit_config['commands']['after']);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run PHP code autofixing.
     *
     * @command toolkit:run-phpcbf
     *
     * @aliases tk-phpcbf
     */
    public function toolkitRunPhpcbf()
    {
        $phpcbf_bin = $this->getBin('phpcbf');
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');
        $this->toolkitCheckPhpcsRequirements();
        return $this->taskExec("$phpcbf_bin --standard=$config_file")->run();
    }

    /**
     * Setup the ESLint configurations and dependencies.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.eslint'.
     *
     * @command toolkit:setup-eslint
     *
     * @option config       The eslint config file.
     * @option ignores      The patterns to ignore.
     * @option drupal-root  The drupal root.
     * @option packages     The npm packages to install.
     * @option force        If true, the config file will be deleted.
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toolkitSetupEslint(array $options = [
        'config' => InputOption::VALUE_OPTIONAL,
        'ignores' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'drupal-root' => InputOption::VALUE_OPTIONAL,
        'packages' => InputOption::VALUE_OPTIONAL,
        'force' => false,
    ])
    {
        $actions = false;
        $config = $options['config'];
        if ($options['force'] && file_exists($config)) {
            $actions = true;
            $this->taskExec('rm')->arg($config)->run();
        }

        // Create a package.json if it doesn't exist.
        if (!file_exists('package.json')) {
            $actions = true;
            $this->taskExec('npm ini -y')->run();
            $this->taskExec("npm install --save-dev {$options['packages']} -y")->run();
        }

        // Check if the binary exists.
        try {
            $this->getNodeBin('eslint');
        } catch (TaskException $e) {
            $actions = true;
            $this->taskExec('npm install')->run();
        }

        if (!file_exists($config)) {
            $actions = true;
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
            $drupal_core = './' . $options['drupal-root'] . '/core';
            if (file_exists($drupal_core)) {
                // Add the drupal core eslint if it exists.
                $drupal_eslint = './' . $options['drupal-root'] . '/core/.eslintrc.json';
                if (file_exists($drupal_eslint)) {
                    $data['extends'] = $drupal_eslint;
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
     * Run lint YAML.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.eslint'.
     *
     * @command toolkit:lint-yaml
     *
     * @option config     The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     *
     * @aliases tly, tk-yaml
     *
     * @usage --extensions='.yml' --options='fix no-eslintrc'
     */
    public function toolkitLintYaml(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        return $this->toolkitRunEsLint($options['config'], $options['extensions'], $options['options']);
    }

    /**
     * Run lint JS.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.eslint'.
     *
     * @command toolkit:lint-js
     *
     * @option config     The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     *
     * @aliases tljs, tk-js
     *
     * @usage --extensions='.js' --options='fix no-eslintrc'
     */
    public function toolkitLintJs(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        return $this->toolkitRunEsLint($options['config'], $options['extensions'], $options['options']);
    }

    /**
     * Execute the eslint.
     *
     * @param string $config
     *   The eslint config file.
     * @param array $extensions
     *   The extensions to check.
     * @param string $options
     *   Extra options for the command.
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

        $tasks[] = $this->taskExec($this->getNodeBinPath('eslint'))->options($opts)->arg('.');

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run lint PHP.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.php'.
     *
     * @command toolkit:lint-php
     *
     * @option exclude     The eslint config file.
     * @option extensions  The extensions to check.
     * @option options     Extra options for the command without -- (only options with no value).
     *
     * @aliases tlp, tk-php
     */
    public function toolkitLintPhp(array $options = [
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'exclude' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
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

        return $this->collectionBuilder()->addTask($task->rawArg('.'));
    }

    /**
     * Run Blackfire.
     *
     * @command toolkit:run-blackfire
     *
     * @aliases tbf, tk-bfire
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toolkitRunBlackfire()
    {
        $base_url = $this->getConfig()->get('drupal.base_url');
        $project_id = $this->getConfig()->get('toolkit.project_id');
        $problems = [];
        if (!getenv('BLACKFIRE_SERVER_ID') || !getenv('BLACKFIRE_SERVER_TOKEN')) {
            $problems[] = 'Missing environment variables: BLACKFIRE_SERVER_ID, BLACKFIRE_SERVER_TOKEN, skipping.';
        }
        if (!getenv('BLACKFIRE_CLIENT_ID') || !getenv('BLACKFIRE_CLIENT_TOKEN')) {
            $problems[] = 'Missing environment variables: BLACKFIRE_CLIENT_ID, BLACKFIRE_CLIENT_TOKEN, skipping.';
        }

        // Confirm that blackfire is properly installed.
        $test = $this->taskExec('which blackfire')->silent(true)
            ->run()->getMessage();
        if (strpos($test, 'not found') !== false) {
            $problems[] = 'The Blackfire is not installed, skipping.';
        }

        // Make sure that the blackfire agent is properly configured.
        $config = $this->taskExec('cat /etc/blackfire/agent | grep server-id=')
            ->silent(true)->run()->getMessage();
        if ($config === 'server-id=') {
            $this->taskExec('blackfire agent:config')->run();
            $this->taskExec('service blackfire-agent restart')->run();
        }

        if (!empty($problems)) {
            $this->say("Problems found:\n" . implode("\n", $problems));
            return new ResultData(0);
        }

        $command = "blackfire --json curl $base_url";

        // Get the list of pages to check and prevent duplicates.
        $pages = $this->getConfig()->get('toolkit.test.blackfire.pages');
        $pages = array_unique($pages);

        // Limit the pages up to 10 items.
        $pages = array_slice((array) $pages, 0, 10);
        foreach ($pages as $page) {
            $this->say("Checking page: {$base_url}{$page}");

            $raw = $this->taskExec($command . $page)
                ->silent(true)->run()->getMessage();
            $result = json_decode($raw, true);

            if (empty($result['_links']['graph_url']['href'])) {
                $this->say('Something went wrong, please contact the QA team.');
                return new ResultData(0);
            }

            $data = [];
            $data['graph'] = $result['_links']['graph_url']['href'];
            $data['timeline'] = $result['_links']['timeline_url']['href'];
            $data['recommendation'] = $data['graph'] . '?settings%5BtabPane%5D=recommendations';
            $data['cpu_time'] = $result['envelope']['cpu'] . 'ms';
            $data['wall_time'] = $result['envelope']['wt'] . 'ms';
            $data['io_wait'] = $result['envelope']['io'] . 'ms';
            $data['memory'] = ToolCommands::formatBytes($result['envelope']['pmu']);
            $data['sql'] = sprintf(
                "%sms %srq",
                $result['arguments']['io.db.query']['*']['wt'],
                $result['arguments']['io.db.query']['*']['ct']
            );
            $data['network'] = sprintf(
                '%s %s %s',
                !empty($result['envelope']['nw']) ? ToolCommands::formatBytes($result['envelope']['nw']) : 'n/a',
                !empty($result['envelope']['nw_in']) ? ToolCommands::formatBytes($result['envelope']['nw_in']) : 'n/a',
                !empty($result['envelope']['nw_out']) ? ToolCommands::formatBytes($result['envelope']['nw_out']) : 'n/a'
            );

            // Print the relevant information.
            $msg = sprintf(
                "Memory:\t\t%s\nWall Time:\t%s\nI/O Wait:\t%s\nCPU Time:\t%s\nNetwork:\t%s\nSQL:\t\t%s",
                $data['memory'],
                $data['wall_time'],
                $data['io_wait'],
                $data['cpu_time'],
                $data['network'],
                $data['sql']
            );
            $this->writeln($msg);

            // Handle repo name.
            if (empty($repo = getenv('DRONE_REPO'))) {
                $repo = getenv('CI_PROJECT_NAME');
            }
            if (empty($ci_url = getenv('DRONE_BUILD_LINK'))) {
                $ci_url = getenv('CI_PIPELINE_URL');
            }

            // Send payload to QA website.
            $url = Website::url();
            if (!empty($repo)) {
                $payload = [
                    '_links' => [
                        'type' => [
                            'href' => $url . '/rest/type/node/blackfire',
                        ],
                    ],
                    'type' => [['target_id' => 'blackfire']],
                    'title' => [['value' => "Profiling: $project_id"]],
                    'body' => [['value' => $raw]],
                    'field_blackfire_repository' => [['value' => $repo]],
                    'field_blackfire_page' => [['value' => $page]],
                    'field_blackfire_ci_cd_url' => [['value' => $ci_url]],
                    'field_blackfire_graph_url' => [['value' => $data['graph']]],
                    'field_blackfire_timeline_url' => [['value' => $data['timeline']]],
                    'field_blackfire_recomendations' => [['value' => $data['recommendation']]],
                    'field_blackfire_memory' => [['value' => $data['memory']]],
                    'field_blackfire_wall_time' => [['value' => $data['wall_time']]],
                    'field_blackfire_io_wait' => [['value' => $data['io_wait']]],
                    'field_blackfire_cpu_time' => [['value' => $data['cpu_time']]],
                    'field_blackfire_network' => [['value' => $data['network']]],
                    'field_blackfire_sql' => [['value' => $data['sql']]],
                    'field_blackfire_commit_hash' => [['value' => getenv('DRONE_COMMIT') ?? '']],
                    'field_blackfire_commit_link' => [['value' => getenv('DRONE_PULL_REQUEST') ?? '']],
                    'field_blackfire_pr' => [['value' => getenv('DRONE_COMMIT_LINK') ?? '']],
                ];
                $basicAuth = Website::basicAuth();
                $payload_response = Website::post($payload, $basicAuth);
                if (!empty($payload_response) && $payload_response === '201') {
                    $this->writeln("Payload sent to QA website: $payload_response");
                } else {
                    $this->writeln('Fail to send the payload, HTTP code: ' . $payload_response);
                }
                $this->writeln('');
            }
        }

        return new ResultData(0);
    }

}
