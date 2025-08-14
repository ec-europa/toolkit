<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\InstalledVersions;
use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\AbortTasksException;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestsCommands.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @return void
     *   The toolkit setup check configuration status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitSetupPhpcs()
    {
        $config = $this->getConfig();
        $configFile = $config->get('toolkit.test.phpcs.config');
        if (file_exists($configFile)) {
            $this->taskExec('rm')->arg($configFile)->run();
        }

        $phpcsXml = new \DOMDocument('1.0', 'UTF-8');
        $phpcsXml->formatOutput = true;
        // Root element.
        $root = $phpcsXml->createElement('ruleset');
        $root->setAttribute('name', 'QA');
        $phpcsXml->appendChild($root);
        $root->appendChild($phpcsXml->createElement('description', 'QA PHPcs Ruleset'));

        // Handle standards.
        $root->appendChild($phpcsXml->createComment(' Standards. '));
        if (!empty($standards = $config->get('toolkit.test.phpcs.standards'))) {
            foreach ($standards as $standard) {
                $element = $phpcsXml->createElement('rule');
                $element->setAttribute('ref', $standard);
                $root->appendChild($element);
            }
        }
        $root->appendChild($phpcsXml->createComment(' Arguments. '));
        // Handle file extensions.
        if (!empty($extensions = $config->get('toolkit.test.phpcs.triggered_by'))) {
            $element = $phpcsXml->createElement('arg');
            $element->setAttribute('name', 'extensions');
            $element->setAttribute('value', implode(',', array_values($extensions)));
            $root->appendChild($element);
        }
        // Handle argument report.
        $element = $phpcsXml->createElement('arg');
        $element->setAttribute('name', 'report');
        $element->setAttribute('value', 'full');
        $root->appendChild($element);
        // Handle argument color.
        $element = $phpcsXml->createElement('arg');
        $element->setAttribute('name', 'colors');
        $root->appendChild($element);
        // Handle argument progress.
        $element = $phpcsXml->createElement('arg');
        $element->setAttribute('value', 'p');
        $root->appendChild($element);
        // Handle show sniffs.
        if ($config->get('toolkit.test.phpcs.show_sniffs') === true) {
            $element = $phpcsXml->createElement('arg');
            $element->setAttribute('value', 's');
            $root->appendChild($element);
        }
        // Handle the files.
        $root->appendChild($phpcsXml->createComment(' Files to check. '));
        if (!empty($files = $config->get('toolkit.test.phpcs.files'))) {
            $files = is_string($files) ? explode(',', $files) : $files;
            Toolkit::filterFolders($files);
            foreach ($files as $file) {
                $root->appendChild($phpcsXml->createElement('file', $file));
            }
        } else {
            $root->appendChild($phpcsXml->createElement('file', '.'));
        }
        // Handle exclude patterns.
        $root->appendChild($phpcsXml->createComment(' Exclude patterns. '));
        if (!empty($ignores = $config->get('toolkit.test.phpcs.ignore_patterns'))) {
            foreach ($ignores as $ignore) {
                $root->appendChild($phpcsXml->createElement('exclude-pattern', $ignore));
            }
        }

        $root->appendChild($phpcsXml->createComment(' Add your custom rules after this line. '));
        $this->taskWriteToFile($configFile)
            ->text($phpcsXml->saveXML())->run();
    }

    /**
     * Run PHP code sniffer.
     *
     * @command toolkit:test-phpcs
     *
     * @option junit  Whether to export results as junit.
     *
     * @aliases tk-phpcs
     *
     * @return int|\Robo\ResultData
     *   The toolkit run phpcs task status.
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
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:test-phpmd
     *
     * @option config          The config file.
     * @option format          The format to use.
     * @option ignore_patterns An array with ignore patterns.
     * @option triggered_by    An array with extensions to check.
     * @option files           An array with paths to check.
     * @option junit           Whether to export results as junit.
     *
     * @aliases tk-phpmd
     *
     * @return int
     *   The toolkit phpmd task status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toolkitTestPhpmd(ConsoleIO $io, array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'format' => InputOption::VALUE_REQUIRED,
        'ignore_patterns' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'triggered_by' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'files' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
    ])
    {
        $execOptions = [];
        Toolkit::ensureArray($options['files']);
        Toolkit::ensureArray($options['ignore_patterns']);
        Toolkit::ensureArray($options['triggered_by']);

        if (!file_exists($options['config'])) {
            $this->output->writeln('Could not find the ruleset file, the default will be created in the project root.');
            $this->_copy(Toolkit::getToolkitRoot() . '/resources/phpmd.xml', $options['config']);
        }

        if (!empty($options['ignore_patterns'])) {
            $execOptions['exclude'] = implode(',', $options['ignore_patterns']);
        }
        if (!empty($options['triggered_by'])) {
            $execOptions['suffixes'] = implode(',', $options['triggered_by']);
        }
        if ($this->isJunit()) {
            $options['format'] = 'json';
            JunitXmlGenerator::addTestSuite('PHPmd');
        }

        Toolkit::filterFolders($options['files']);
        $files = implode(',', $options['files']);

        $exec = $this->taskExec($this->getBin('phpmd'))
            ->args([$files, $options['format'], $options['config']])
            ->options($execOptions);

        if ($this->isJunit()) {
            $result = $exec->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run();
            $findings = json_decode($result->getMessage(), true);
            if (!empty($findings['files'])) {
                foreach ($findings['files'] as $find) {
                    $io->writeln('FILE: ' . $find['file']);
                    $io->writeln(str_repeat('-', strlen('FILE: ' . $find['file'])));
                    foreach ($find['violations'] as $violation) {
                        $message = sprintf('%d | VIOLATION | %s', $violation['beginLine'], $violation['description']);
                        $this->writeln($message);
                        JunitXmlGenerator::addResult('PHPmd', $find['file'], $message);
                    }
                    $io->newLine();
                }
            }
            JunitXmlGenerator::generate('junit-phpmd.xml');
        } else {
            $result = $exec->run();
        }

        return $result->getExitCode();
    }

    /**
     * Run PHP code sniffer within GrumPHP.
     *
     * @throws \Robo\Exception\TaskException
     *
     * @return int|\Robo\ResultData
     *   The toolkit run grumphp task status.
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

        $composer = $this->getJson('composer.json');
        if (isset($composer['extra']['grumphp']['config-default-path'])) {
            $configDefaultPath = $composer['extra']['grumphp']['config-default-path'];
            $this->say('You should remove the following from your composer.json extra array:');
            echo "\n\"grumphp\": {\n    \"config-default-path\": \"$configDefaultPath\"\n}\n\n";
        }

        if ($containsQaConventions) {
            return $this->taskExec("$bin run")->run();
        } else {
            $this->say('All Drupal projects in the ec-europa namespace need to use Quality Assurance provided standards.');
            $this->say('Your configuration has to import the resource vendor/ec-europa/qa-automation/dist/qa-conventions.yml.');
            $this->say('For more information visit: https://ec-europa.github.io/toolkit/guide/testing-project.html#phpcs-testing');
            $this->say('Add the following lines to your grumphp.yml.dist:');
            echo "\nimports:\n  - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }\n\n";
            return new ResultData(1);
        }
    }

    /**
     * Run PHP code sniffer.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpcs'.
     *
     * @return \Robo\Result
     *   The toolkit run phpcs code sniffer.
     */
    protected function toolkitRunPhpcs()
    {
        $config = $this->getConfig();
        $phpcsBin = $this->getBin('phpcs');
        $configFile = $config->get('toolkit.test.phpcs.config');

        $this->toolkitCheckPhpcsRequirements();

        $options = '';
        if ($config->get('toolkit.test.phpcs.ignore_annotations') === true) {
            $options .= ' --ignore-annotations';
        }
        if ($this->isJunit()) {
            $options .= ' --report=junit --report-file=' . JunitXmlGenerator::$dir . '/junit-phpcs.xml';
        }
        return $this->taskExec("$phpcsBin --standard=$configFile$options")
            ->run();
    }

    /**
     * Make sure that the config file exists and configuration is correct.
     *
     * @command toolkit:check-phpcs-requirements
     *
     * @return mixed
     *   The status if exists config file.
     */
    public function toolkitCheckPhpcsRequirements()
    {
        $configFile = $this->getConfig()->get('toolkit.test.phpcs.config');
        if (!file_exists($configFile)) {
            $this->say('Calling toolkit:setup-phpcs.');
            $this->toolkitSetupPhpcs();
        }

        // Skip standards check for Toolkit.
        if (getcwd() === Toolkit::getToolkitRoot()) {
            return;
        }

        // Make sure the required standards are present.
        $standards = [
            './vendor/squizlabs/php_codesniffer/CodeSniffer.conf',
        ];
        $rules = [];
        $data = simplexml_load_file($configFile);
        foreach ($data->rule as $item) {
            if (isset($item['ref'])) {
                $rules[] = (string) $item['ref'];
            }
        }
        /*if ($diff = array_diff($standards, $rules)) {
            throw new AbortTasksException("The following standards are missing, please add them to the configuration file '$configFile'.\n" . implode("\n", $diff));
        }*/
    }

    /**
     * Run PHPStan.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpstan'.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:test-phpstan
     *
     * @option config       The path to the config file.
     * @option level        The level of rule options.
     * @option files        The files to check.
     * @option memory-limit The PHP memory limit.
     * @option options      Extra options for the command without -- (only options with no value).
     * @option junit        Whether to export results as junit.
     *
     * @aliases tk-phpstan
     *
     * @usage --memory-limit='4G' --options='debug'
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The toolkit test phpstan task status.
     */
    public function toolkitTestPhpstan(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'level' => InputOption::VALUE_REQUIRED,
        'files' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'memory-limit' => InputOption::VALUE_OPTIONAL,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        Toolkit::ensureArray($options['files']);
        Toolkit::filterFolders($options['files']);

        $config = $this->getConfig();
        $ignoreErrors = $config->get('toolkit.test.phpstan.ignore_errors');
        $includes = $config->get('toolkit.test.phpstan.includes');
        Toolkit::filterFolders($includes);
        $ignores = $config->get('toolkit.test.phpstan.ignores');
        Toolkit::filterFolders($ignores);
        if (!$options['memory-limit']) {
            $options['memory-limit'] = ini_get('memory_limit');
        }
        $tasks = [];

        // If the config file is not found, generate a new one.
        if (!file_exists($options['config'])) {
            $configContent = [
                'parameters' => [
                    'level' => $options['level'],
                    'paths' => array_values($options['files']),
                    'excludePaths' => $ignores,
                    'ignoreErrors' => $ignoreErrors,
                    'parallel' => [
                        'maximumNumberOfProcesses' => 1,
                    ],
                ],
            ];
            if (!InstalledVersions::isInstalled('phpstan/extension-installer')) {
                $configContent['includes'] = $includes;
            }
            $tasks[] = $this->taskWriteToFile($options['config'])
                ->text(Yaml::dump($configContent, 10, 2));
        }

        $exec = $this->taskExec($this->getBin('phpstan'))
            ->arg('analyse')
            ->options([
                'memory-limit' => $options['memory-limit'],
                'configuration' => $options['config'],
            ], '=');

        if (!empty($options['options'])) {
            $extraOptions = array_fill_keys(explode(' ', $options['options']), null);
            $exec->options($extraOptions);
        }

        if ($this->isJunit()) {
            $exec->option('error-format', 'junit');
            $exec->rawArg('> ' . JunitXmlGenerator::$dir . '/junit-phpstan.xml');
        }

        $tasks[] = $exec;
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run Behat tests.
     *
     * Check configurations at config/default.yml - 'toolkit.test.behat'.
     * Accept commands to run before and/or after the Behat tests.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:test-behat
     *
     * @option from     The dist config file (behat.yml.dist).
     * @option to       The destination config file (behat.yml).
     * @option profile  The profile to execute.
     * @option suite    The suite to execute, default runs all suites of profile.
     * @option options  Extra options for the command without -- (only options with no value).
     * @option junit    Whether to export results as junit.
     *
     * @aliases tk-behat, tb
     *
     * @usage --profile='prod' --options='strict stop-on-failure'
     *
     * @return int|\Robo\ResultData
     *   The check toolkit test behat status.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toolkitTestBehat(array $options = [
        'from' => InputOption::VALUE_OPTIONAL,
        'to' => InputOption::VALUE_OPTIONAL,
        'profile' => InputOption::VALUE_OPTIONAL,
        'suite' => InputOption::VALUE_OPTIONAL,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
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
            $this->taskExecute($commands)->run()->stopOnFail();
        }

        $this->taskProcess($options['from'], $options['to'])->run();

        $result = $this->taskExec($behatBin)->options($execOpts + ['dry-run' => null], '=')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        if (str_contains(trim($result), 'No scenarios')) {
            $this->say("No Scenarios found for profile {$execOpts['profile']}, please create at least one Scenario.");
            return new ResultData(1);
        }

        $exec = $this->taskExec($behatBin)->options($execOpts, '=');

        if ($this->isJunit()) {
            $exec->option('format', 'progress');
            $exec->option('out', 'std');
            $exec->option('format', 'junit');
            $exec->option('out', 'behat-xml');
        }

        $result = $exec->run();

        // As behat can only export junit into multiple files, we need to merge them into a single file.
        if ($this->isJunit()) {
            JunitXmlGenerator::mergeFiles('junit-behat.xml', 'behat-xml');
        }

        // Execute a list of commands to run after tests.
        if ($commands = $this->getConfig()->get('toolkit.test.behat.commands.after')) {
            $this->taskExecute($commands)->run()->stopOnFail();
        }

        return $result->getExitCode();
    }

    /**
     * Run PHPUnit tests.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpunit'.
     * Accept commands to run before and/or after the PHPUnit tests.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:test-phpunit
     *
     * @option execution The execution type (default or parallel).
     * @option from      The dist config file (phpunit.xml.dist).
     * @option to        The destination config file (phpunit.xml).
     * @option testsuite Filter which testsuite to run.
     * @option group     Only runs tests from the specified group(s).
     * @option covers    Only runs tests annotated with "@covers <name>".
     * @option uses      Only runs tests annotated with "@uses <name>".
     * @option filter    Filter which tests to run.
     * @option options   Extra options for the command without -- (only options with no value).
     * @option printer   If set, use printer defined in config toolkit.test.phpunit.printer.
     * @option junit     Whether to export results as junit.
     *
     * @aliases tk-phpunit tp
     *
     * @usage --options='stop-on-error process-isolation do-not-cache-result'
     * @usage --group=Example
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The toolkit phpunit task status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitTestPhpunit(array $options = [
        'execution' => InputOption::VALUE_REQUIRED,
        'from' => InputOption::VALUE_REQUIRED,
        'to' => InputOption::VALUE_REQUIRED,
        'testsuite' => InputOption::VALUE_REQUIRED,
        'group' => InputOption::VALUE_REQUIRED,
        'covers' => InputOption::VALUE_REQUIRED,
        'uses' => InputOption::VALUE_REQUIRED,
        'filter' => InputOption::VALUE_REQUIRED,
        'options' => InputOption::VALUE_REQUIRED,
        'printer' => InputOption::VALUE_NONE,
    ])
    {
        if (!file_exists($options['to'])) {
            if (!file_exists($options['from'])) {
                $this->say('PHUnit configuration not found, skipping.');
                return $this->collectionBuilder()->addTaskList([]);
            }
            $this->taskProcess($options['from'], $options['to'])->run();
        }
        $tasks = [];
        $phpunitConfig = $this->getConfig()->get('toolkit.test.phpunit');

        // Execute a list of commands to run before tests.
        if (!empty($phpunitConfig['commands']['before'])) {
            $tasks[] = $this->taskExecute($phpunitConfig['commands']['before']);
        }

        if ($options['execution'] == 'parallel') {
            $tasks[] = $this->toolkitTestPhpunitParallelTask($options);
        } else {
            $task = $this->taskExec($this->getBin('phpunit'));
            if (!empty($options['options'])) {
                $options['options'] = str_replace('--', '', $options['options']);
                $opts = array_fill_keys(explode(' ', $options['options']), null);
                $task->options($opts, '=');
            }
            foreach (['testsuite', 'group', 'covers', 'uses', 'filter'] as $item) {
                if ($options[$item]) {
                    $task->option($item, $options[$item], '=');
                }
            }
            if ($options['printer'] === true) {
                $task->option('printer', $phpunitConfig['printer'], '=');
            }
            if ($this->isJunit()) {
                $task->option('log-junit', JunitXmlGenerator::$dir . '/junit-phpunit.xml');
            }
            $tasks[] = $task;
        }

        // Execute a list of commands to run after tests.
        if (!empty($phpunitConfig['commands']['after'])) {
            $tasks[] = $this->taskExecute($phpunitConfig['commands']['after']);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Returns the task to execute PHPUnit in parallel.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @return \Robo\Task\Base\ParallelExec
     *   Task to execute PHPUnit in parallel.
     */
    private function toolkitTestPhpunitParallelTask(array $options)
    {
        $phpunitBin = $this->getBin('phpunit');
        $result = $this->taskExec($phpunitBin)->option('list-suites')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        preg_match_all('/ - ([\w\s]+)/', $result, $matches);
        $parallel = $this->taskParallelExec()->printOutput();
        if (!empty($matches[1])) {
            $opts = ' ';
            if (!empty($options['options'])) {
                $options['options'] = str_replace('--', '', $options['options']);
                $opts .= '--' . implode(' --', explode(' ', $options['options']));
            }
            foreach (['testsuite', 'group', 'covers', 'uses', 'filter'] as $item) {
                if ($options[$item]) {
                    $opts .= " --$item=$options[$item]";
                }
            }
            if ($options['printer'] === true) {
                $opts .= " --printer=" . $this->getConfig()->get('toolkit.test.phpunit.printer');
            }
            foreach (array_map('trim', $matches[1]) as $suite) {
                if (strlen($suite) > 2) {
                    $parallel->process("$phpunitBin --testsuite='$suite'$opts");
                }
            }
        } else {
            $this->writeln('No items found.');
        }
        return $parallel;
    }

    /**
     * Run PHP code autofixing.
     *
     * @command toolkit:run-phpcbf
     *
     * @aliases tk-phpcbf
     *
     * @return mixed
     *   The run phpcbf code autofixing.
     */
    public function toolkitRunPhpcbf()
    {
        $phpcbfBin = $this->getBin('phpcbf');
        $configFile = $this->getConfig()->get('toolkit.test.phpcs.config');
        $this->toolkitCheckPhpcsRequirements();
        return $this->taskExec("$phpcbfBin --standard=$configFile")->run();
    }

}
